<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\Process\Process;

class MenuImageExtractionService
{
    /**
     * @return array<int, array{name: string, description: string, category: string, price_per_unit: float, stock_quantity: int, unit: string, moq: int, confidence?: float}>
     */
    public function extractDraftProducts(UploadedFile $uploadedFile): array
    {
        $sourcePath = $uploadedFile->getRealPath();

        if (!$sourcePath) {
            throw new RuntimeException('Unable to access the uploaded image file.');
        }

        // Try to get hOCR output for confidence, fallback to plain text
        $hocr = $this->extractTextFromImage($sourcePath, true);
        $text = $this->extractTextFromImage($sourcePath, false);
        $items = $this->parseProductsFromText($text, $hocr);

        if (count($items) === 0) {
            throw new RuntimeException('No menu-like products were detected. Please try a clearer image or add products manually.');
        }

        return $items;
    }

    /**
     * Extract text or hOCR from image. If $hocr is true, returns hOCR XML, else plain text.
     */
    private function extractTextFromImage(string $sourcePath, bool $hocr = false): string
    {
        $tesseractBinary = $this->resolveTesseractBinary();

        $args = [
            $tesseractBinary,
            $sourcePath,
            'stdout',
            '--psm',
            '6',
        ];
        if ($hocr) {
            $args[] = 'hocr';
        }
        $process = new Process($args);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = trim((string) $process->getErrorOutput());
            if ($errorOutput === '') {
                $errorOutput = 'OCR command failed.';
            }
            throw new RuntimeException(
                'Image text extraction failed using "' . $tesseractBinary . '". Ensure Tesseract OCR is installed in the same runtime as PHP (for Docker/Sail, install it inside the app container). ' . $errorOutput
            );
        }

        $text = trim((string) $process->getOutput());
        if ($text === '') {
            throw new RuntimeException('No readable text was detected from the uploaded menu image.');
        }
        return $text;
    }

    private function resolveTesseractBinary(): string
    {
        $configured = (string) env('TESSERACT_BINARY', '');
        if ($configured !== '') {
            return $configured;
        }

        $commonPaths = [
            '/usr/bin/tesseract',
            '/usr/local/bin/tesseract',
            'tesseract',
        ];

        foreach ($commonPaths as $path) {
            if ($path === 'tesseract') {
                return $path;
            }

            if (is_executable($path)) {
                return $path;
            }
        }

        throw new RuntimeException(
            'Tesseract OCR binary was not found. Set TESSERACT_BINARY in .env or install tesseract-ocr in the PHP runtime/container.'
        );
    }

    /**
     * @param string $text
     * @param string|null $hocr
     * @return array<int, array{name: string, description: string, category: string, price_per_unit: float, stock_quantity: int, unit: string, moq: int, confidence?: float}>
     */
    private function parseProductsFromText(string $text, ?string $hocr = null): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $items = [];
        $seen = [];
        $confidences = $hocr ? $this->extractLineConfidencesFromHocr($hocr) : [];
        $confidenceCursor = 0;
        $pendingTitle = null;
        $pendingConfidence = null;

        foreach ($lines as $i => $rawLine) {
            $line = trim((string) preg_replace('/\s+/', ' ', $rawLine));
            if ($line === '' || mb_strlen($line) < 4) {
                continue;
            }

            $match = $this->extractNameAndPrice($line);
            if ($match === null) {
                if ($this->isLikelyTitleLine($line)) {
                    $pendingTitle = $this->cleanTitle($line);
                    $pendingConfidence = $confidences[$confidenceCursor] ?? null;
                    $confidenceCursor++;
                }

                continue;
            }

            $lineName = $this->cleanTitle($match['name']);
            $price = $match['price'];

            if ($lineName === '' || $price < 0) {
                continue;
            }

            $name = $lineName;
            $description = '';
            $confidence = $confidences[$confidenceCursor] ?? null;
            $confidenceCursor++;

            if ($this->isLikelyDescriptionLine($lineName) && is_string($pendingTitle) && $pendingTitle !== '') {
                $name = $pendingTitle;
                $description = $lineName;
                $confidence = $pendingConfidence ?? $confidence;
            }

            $nextLine = $lines[$i + 1] ?? null;
            if ($description === '' && is_string($nextLine)) {
                $nextNormalized = trim((string) preg_replace('/\s+/', ' ', $nextLine));
                if (
                    $nextNormalized !== ''
                    && $this->extractNameAndPrice($nextNormalized) === null
                    && $this->isLikelyDescriptionLine($nextNormalized)
                ) {
                    $description = mb_substr($nextNormalized, 0, 180);
                }
            }

            $pendingTitle = null;
            $pendingConfidence = null;

            $normalizedName = mb_strtolower($name);
            if (isset($seen[$normalizedName])) {
                continue;
            }

            $seen[$normalizedName] = true;

            $item = [
                'name' => $name,
                'description' => $description,
                'category' => $this->guessCategory($name),
                'price_per_unit' => $price,
                'stock_quantity' => 10,
                'unit' => 'cup',
                'moq' => 1,
            ];
            if (is_float($confidence)) {
                $item['confidence'] = $confidence;
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return array{name: string, price: float}|null
     */
    private function extractNameAndPrice(string $line): ?array
    {
        $name = null;
        $price = null;

        if (preg_match('/^(?<name>[A-Za-z0-9][A-Za-z0-9\-\'\"\.,&()\/\s]{1,140}?)\s+(?<price>(?:PHP|P|₱)?\s*\d{1,5}(?:[\.,]\d{1,2})?)$/iu', $line, $matches)) {
            $name = trim((string) ($matches['name'] ?? ''));
            $price = $this->normalizePrice((string) ($matches['price'] ?? ''));
        } elseif (preg_match('/^(?<price>(?:PHP|P|₱)?\s*\d{1,5}(?:[\.,]\d{1,2})?)\s*[-:\|]\s*(?<name>.+)$/iu', $line, $matches)) {
            $name = trim((string) ($matches['name'] ?? ''));
            $price = $this->normalizePrice((string) ($matches['price'] ?? ''));
        } elseif (preg_match('/^(?<name>.+?)\s*[-:\|]\s*(?<price>(?:PHP|P|₱)?\s*\d{1,5}(?:[\.,]\d{1,2})?)$/iu', $line, $matches)) {
            $name = trim((string) ($matches['name'] ?? ''));
            $price = $this->normalizePrice((string) ($matches['price'] ?? ''));
        } elseif (preg_match('/^(?<name>.+?)\s+(?<price>\d{2,5})$/iu', $line, $matches)) {
            $name = trim((string) ($matches['name'] ?? ''));
            $price = $this->normalizePrice((string) ($matches['price'] ?? ''));
        }

        if (!$name || $price === null) {
            return null;
        }

        return [
            'name' => $name,
            'price' => $price,
        ];
    }

    private function cleanTitle(string $line): string
    {
        $cleaned = trim((string) preg_replace('/\s+/', ' ', $line));
        $cleaned = trim($cleaned, "-:|\t\n\r\0\x0B ");
        return mb_substr($cleaned, 0, 140);
    }

    private function isLikelyTitleLine(string $line): bool
    {
        $normalized = $this->cleanTitle($line);
        if ($normalized === '' || mb_strlen($normalized) < 3) {
            return false;
        }

        if (preg_match('/(?:php|₱|\d{2,5})/iu', $normalized)) {
            return false;
        }

        $wordCount = count(array_filter(explode(' ', $normalized)));
        if ($wordCount > 7) {
            return false;
        }

        return true;
    }

    private function isLikelyDescriptionLine(string $line): bool
    {
        $normalized = mb_strtolower($this->cleanTitle($line));
        if ($normalized === '') {
            return false;
        }

        $wordCount = count(array_filter(explode(' ', $normalized)));
        if ($wordCount >= 5) {
            return true;
        }

        if (str_contains($normalized, ',') || str_contains($normalized, ' with ') || str_contains($normalized, ' and ')) {
            return true;
        }

        $descriptorTerms = [
            'lettuce', 'tomato', 'onion', 'cheese', 'patty', 'fries', 'sauce', 'spread', 'crispy', 'special',
        ];

        foreach ($descriptorTerms as $term) {
            if (str_contains($normalized, $term)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extracts confidence per line from hOCR output (if available).
     * @param string $hocr
     * @return array<int, float>  // line index => confidence (0-1)
     */
    private function extractLineConfidencesFromHocr(string $hocr): array
    {
        $confidences = [];
        if (!preg_match_all('/<span\s+class=["\']ocr_line["\'][^>]*title=["\'][^"\']*x_wconf\s+(\d+)[^"\']*["\'][^>]*>.*?<\/span>/si', $hocr, $matches, PREG_SET_ORDER)) {
            return $confidences;
        }
        $i = 0;
        foreach ($matches as $match) {
            $wconf = isset($match[1]) ? (int)$match[1] : 0;
            $confidences[$i++] = round($wconf / 100, 2);
        }
        return $confidences;
    }

    private function normalizePrice(string $rawPrice): ?float
    {
        $value = preg_replace('/[^0-9\.,]/', '', $rawPrice);
        if (!$value) {
            return null;
        }

        $value = str_replace(',', '.', $value);
        if (!is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function guessCategory(string $name): string
    {
        $needle = mb_strtolower($name);
        if (str_contains($needle, 'bean') || str_contains($needle, 'arabica') || str_contains($needle, 'robusta')) {
            return 'Coffee Beans';
        }
        if (str_contains($needle, 'latte') || str_contains($needle, 'americano') || str_contains($needle, 'espresso') || str_contains($needle, 'mocha') || str_contains($needle, 'brew') || str_contains($needle, 'coffee')) {
            return 'Brewed Coffee';
        }
        if (str_contains($needle, 'tea') || str_contains($needle, 'matcha') || str_contains($needle, 'chai')) {
            return 'Tea';
        }
        if (str_contains($needle, 'sandwich') || str_contains($needle, 'panini') || str_contains($needle, 'toast') || str_contains($needle, 'bagel')) {
            return 'Sandwiches';
        }
        if (str_contains($needle, 'cake') || str_contains($needle, 'pastry') || str_contains($needle, 'croissant') || str_contains($needle, 'cookie') || str_contains($needle, 'brownie') || str_contains($needle, 'muffin')) {
            return 'Pastries';
        }
        if (str_contains($needle, 'juice') || str_contains($needle, 'shake') || str_contains($needle, 'smoothie') || str_contains($needle, 'soda') || str_contains($needle, 'lemonade')) {
            return 'Cold Drinks';
        }
        return 'Other';
    }
}
