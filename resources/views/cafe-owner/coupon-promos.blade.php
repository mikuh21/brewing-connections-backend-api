@extends('cafe-owner.layouts.app')

@section('title', 'Coupon Promos')

@section('content')
<div
    class="cafe-coupon-promos-page"
    x-data="{
        filter: 'all',
        tableFilter: 'all',
        tableSearch: '',
        createModalOpen: false,
        scannerModalOpen: false,
        qrModalOpen: false,
        analyticsModalOpen: false,
        deleteModalOpen: false,
        scannerInstance: null,
        scannerBusy: false,
        scannerError: '',
        scannerSuccess: '',
        isWebMobile: window.innerWidth < 768,
        establishmentName: @js((auth()->user()->name ?? 'Your Cafe') . "'s Cafe"),
        dailyClaimsChart: null,
        timeOfDayChart: null,
        analyticsRows: [],
        postRoute: @js(route('cafe-owner.coupon-promos.store')),
        redeemScanRoute: @js(route('cafe-owner.coupon-promos.redeem-scan')),
        updateRouteBase: @js(url('/cafe-owner/coupon-promos')),
        createForm: {
            id: null,
            title: @js(old('title', '')),
            description: @js(old('description', '')),
            discount_type: @js(old('discount_type', 'percentage')),
            discount_value: @js(old('discount_value', '')),
            valid_from: @js(old('valid_from', '')),
            valid_until: @js(old('valid_until', '')),
            max_usage: @js(old('max_usage', 1)),
            status: @js(old('status', 'active')),
            isEditing: false
        },
        resetCreateForm() {
            this.createForm.id = null;
            this.createForm.title = '';
            this.createForm.description = '';
            this.createForm.discount_type = 'percentage';
            this.createForm.discount_value = '';
            this.createForm.valid_from = '';
            this.createForm.valid_until = '';
            this.createForm.max_usage = 1;
            this.createForm.status = 'active';
            this.createForm.isEditing = false;
        },
        openCreateModal() {
            this.resetCreateForm();
            this.createModalOpen = true;
            this.$nextTick(() => this.renderCreateQr());
        },
        openEditCoupon(coupon) {
            this.createForm.id = coupon.id;
            this.createForm.title = coupon.title;
            this.createForm.description = coupon.description;
            this.createForm.discount_type = coupon.discount_type;
            this.createForm.discount_value = coupon.discount_value;
            this.createForm.valid_from = coupon.valid_from;
            this.createForm.valid_until = coupon.valid_until;
            this.createForm.max_usage = coupon.max_usage;
            this.createForm.status = coupon.status;
            this.createForm.isEditing = true;
            this.createModalOpen = true;
            this.$nextTick(() => this.renderCreateQr());
        },
        renderCreateQr() {
            if (typeof QRCode === 'undefined') {
                return;
            }

            const previewEl = document.getElementById('qr-preview');
            if (!previewEl) {
                return;
            }

            previewEl.innerHTML = '';
            new QRCode(previewEl, {
                text: this.createForm.title || 'New Coupon Promo',
                width: 180,
                height: 180,
                colorDark: '#3A2E22',
                colorLight: '#F5F0E8'
            });
        },
        downloadAnalyticsReport() {
            if (typeof jspdf === 'undefined') return;

            const { jsPDF } = jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const pageW = doc.internal.pageSize.getWidth();
            const margin = 20;
            const contentW = pageW - margin * 2;
            let y = 20;

            const coupon = this.selectedCoupon;
            const establishment = @js($establishment->name ?? 'BrewHub Cafe');
            const city = @js($establishment->city ?? 'Lipa');

            // Header
            doc.setFontSize(20);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Claim Analytics Report', margin, y);
            y += 8;

            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(122, 105, 87);
            doc.text('Generated: ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), margin, y);
            y += 4;
            doc.text(establishment + ' \u2022 BrewHub \u2022 ' + city, margin, y);
            y += 10;

            // Divider
            doc.setDrawColor(229, 221, 208);
            doc.setLineWidth(0.5);
            doc.line(margin, y, pageW - margin, y);
            y += 10;

            // Coupon Info
            doc.setFontSize(14);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text(coupon.title || 'Untitled Coupon', margin, y);
            y += 6;

            doc.setFontSize(10);
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(122, 105, 87);
            doc.text(coupon.description || '', margin, y);
            y += 6;

            const discountStr = coupon.discount_type === 'percentage'
                ? coupon.discount_value + '% OFF'
                : 'PHP ' + coupon.discount_value + ' OFF';
            doc.text('Discount: ' + discountStr + '    |    Valid: ' + coupon.valid_from + ' \u2013 ' + coupon.valid_until, margin, y);
            y += 12;

            // Summary Cards
            const totalClaims = Number(coupon.used_count || 0);
            const remaining = Math.max(0, (coupon.max_usage || 0) - totalClaims);
            const claimRate = (coupon.max_usage || 0) > 0 ? Math.round((totalClaims / coupon.max_usage) * 100) + '%' : '0%';

            const cardW = (contentW - 8) / 3;
            const cardH = 24;
            const cards = [
                { label: 'Total Claims', value: String(totalClaims) },
                { label: 'Remaining Claims', value: String(remaining) },
                { label: 'Claim Rate', value: claimRate }
            ];

            cards.forEach((card, i) => {
                const cx = margin + i * (cardW + 4);
                doc.setFillColor(250, 247, 241);
                doc.setDrawColor(229, 221, 208);
                doc.roundedRect(cx, y, cardW, cardH, 3, 3, 'FD');

                doc.setFontSize(8);
                doc.setFont('helvetica', 'normal');
                doc.setTextColor(122, 105, 87);
                doc.text(card.label, cx + 4, y + 8);

                doc.setFontSize(16);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(58, 46, 34);
                doc.text(card.value, cx + 4, y + 19);
            });
            y += cardH + 12;

            // Charts
            const drawChart = (canvas, title, x, w) => {
                if (!canvas) return;
                doc.setFontSize(10);
                doc.setFont('helvetica', 'bold');
                doc.setTextColor(58, 46, 34);
                doc.text(title, x, y);

                const chartImg = canvas.toDataURL('image/png');
                const chartH = 48;
                doc.addImage(chartImg, 'PNG', x, y + 3, w, chartH);
                return chartH + 8;
            };

            const chartW = (contentW - 6) / 2;
            const dailyCanvas = document.getElementById('daily-claims-chart');
            const timeCanvas = document.getElementById('time-of-day-chart');

            const h1 = drawChart(dailyCanvas, 'Daily Claims Breakdown', margin, chartW);
            const h2 = drawChart(timeCanvas, 'Claims by Time of Day', margin + chartW + 6, chartW);
            y += Math.max(h1 || 0, h2 || 0) + 8;

            // Recent Claims Table
            doc.setFontSize(10);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(58, 46, 34);
            doc.text('Recent Claims', margin, y);
            y += 6;

            const cols = ['Date & Time', 'Customer', 'Location', 'Discount', 'Status'];
            const colWidths = [38, 36, 36, 30, 24];

            // Table header
            doc.setFillColor(250, 247, 241);
            doc.rect(margin, y, contentW, 7, 'F');
            doc.setFontSize(7);
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(158, 140, 120);
            let tx = margin + 2;
            cols.forEach((col, i) => {
                doc.text(col.toUpperCase(), tx, y + 5);
                tx += colWidths[i];
            });
            y += 8;

            // Table rows
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(58, 46, 34);
            doc.setFontSize(7);

            (this.analyticsRows || []).forEach((row) => {
                if (y > 270) {
                    doc.addPage();
                    y = 20;
                }

                tx = margin + 2;
                const vals = [row.dateTime, row.customer, row.location, row.discount, row.status];
                vals.forEach((val, i) => {
                    doc.text(String(val || ''), tx, y + 4);
                    tx += colWidths[i];
                });

                doc.setDrawColor(240, 233, 222);
                doc.line(margin, y + 6, pageW - margin, y + 6);
                y += 7;
            });

            if (!this.analyticsRows || this.analyticsRows.length === 0) {
                doc.setTextColor(158, 140, 120);
                doc.text('No claims recorded yet.', margin + 2, y + 4);
                y += 7;
            }

            // Footer
            y = Math.max(y + 10, 275);
            if (y > 285) {
                doc.addPage();
                y = 275;
            }
            doc.setFontSize(8);
            doc.setFont('helvetica', 'italic');
            doc.setTextColor(158, 140, 120);
            doc.text('BrewHub \u2022 ' + city + ' \u2014 ' + establishment, pageW / 2, y, { align: 'center' });

            // Save
            const filename = (coupon.title || 'analytics').replace(/[^a-z0-9]/gi, '-') + '-Analytics-Report.pdf';
            doc.save(filename);
        },
        initAnalyticsCharts(couponData) {
            if (typeof Chart === 'undefined') {
                return;
            }

            const analytics = couponData.analytics || {};
            const dailyLabels = Array.isArray(analytics.daily_labels) ? analytics.daily_labels : [];
            const dailyData = Array.isArray(analytics.daily_data) ? analytics.daily_data : [];
            const timeLabels = Array.isArray(analytics.time_labels) ? analytics.time_labels : [];
            const timeData = Array.isArray(analytics.time_data) ? analytics.time_data : [];

            const dailyCtx = document.getElementById('daily-claims-chart');
            const timeCtx = document.getElementById('time-of-day-chart');

            if (!dailyCtx || !timeCtx) {
                return;
            }

            if (this.dailyClaimsChart) {
                this.dailyClaimsChart.destroy();
            }

            if (this.timeOfDayChart) {
                this.timeOfDayChart.destroy();
            }

            this.dailyClaimsChart = new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Claims',
                        data: dailyData,
                        backgroundColor: '#4A6741',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                color: '#E5DDD0'
                            },
                            ticks: {
                                color: '#9E8C78'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5DDD0'
                            },
                            ticks: {
                                color: '#9E8C78',
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            this.timeOfDayChart = new Chart(timeCtx, {
                type: 'line',
                data: {
                    labels: timeLabels,
                    datasets: [{
                        label: 'Claims',
                        data: timeData,
                        borderColor: '#3B82F6',
                        backgroundColor: '#3B82F6',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                color: '#E5DDD0'
                            },
                            ticks: {
                                color: '#9E8C78'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5DDD0'
                            },
                            ticks: {
                                color: '#9E8C78',
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            this.analyticsRows = Array.isArray(analytics.recent_claims) ? analytics.recent_claims : [];
        },
        selectedCoupon: {
            id: null,
            title: '',
            description: '',
            discount_type: '',
            discount_value: 0,
            valid_from: '',
            valid_until: '',
            max_usage: 0,
            used_count: 0,
            status: '',
            qr_code_token: '',
            analytics: {
                daily_labels: [],
                daily_data: [],
                time_labels: [],
                time_data: [],
                recent_claims: []
            }
        },
        init() {
            this.$watch('createModalOpen', (isOpen) => {
                if (isOpen) {
                    this.$nextTick(() => {
                        this.renderCreateQr();
                    });
                }
            });

            this.$watch('qrModalOpen', (isOpen) => {
                if (isOpen) {
                    this.$nextTick(() => {
                        this.renderQrModal();
                    });
                }
            });

            const updateViewportMode = () => {
                this.isWebMobile = window.innerWidth < 768;
            };

            updateViewportMode();
            window.addEventListener('resize', updateViewportMode);
        },
        renderQrModal() {
            const qrDisplay = document.getElementById('qr-display');
            if (!qrDisplay || !this.selectedCoupon.qr_code_token) return;

            // Clear previous QR code
            qrDisplay.innerHTML = '';

            // Generate QR code
            new QRCode(qrDisplay, {
                text: '{{ url("/redeem") }}/' + this.selectedCoupon.qr_code_token,
                width: 220,
                height: 220,
                colorDark: '#3A2E22',
                colorLight: '#F5F0E8',
                correctLevel: QRCode.CorrectLevel.H
            });
        },
        downloadQRCode() {
            if (!this.selectedCoupon || !this.selectedCoupon.qr_code_token) return;

            const coupon = this.selectedCoupon;
            const scale = 2;
            const cardW = 580 * scale;
            const cardH = 960 * scale;
            const padX = 56 * scale;
            const padTop = 64 * scale;
            const qrSize = 340 * scale;
            const bg = '#F0EBE1';
            const radius = 20 * scale;
            const establishmentName = @js($establishment->name ?? 'BrewHub Cafe');
            const city = coupon.city ?? @js($establishment->city ?? 'Lipa');

            // render QR into a temporary element
            const tmp = document.createElement('div');
            tmp.style.position = 'absolute';
            tmp.style.left = '-9999px';
            document.body.appendChild(tmp);
            new QRCode(tmp, {
                text: coupon.qr_code_token,
                width: qrSize,
                height: qrSize,
                colorDark: '#3A2E22',
                colorLight: bg,
                correctLevel: QRCode.CorrectLevel.H
            });

            setTimeout(() => {
                const qrCanvas = tmp.querySelector('canvas');
                const outCanvas = document.createElement('canvas');
                outCanvas.width = cardW;
                outCanvas.height = cardH;
                const ctx = outCanvas.getContext('2d');

                // draw rounded rect background
                ctx.fillStyle = bg;
                ctx.beginPath();
                ctx.moveTo(radius, 0);
                ctx.lineTo(cardW - radius, 0);
                ctx.quadraticCurveTo(cardW, 0, cardW, radius);
                ctx.lineTo(cardW, cardH - radius);
                ctx.quadraticCurveTo(cardW, cardH, cardW - radius, cardH);
                ctx.lineTo(radius, cardH);
                ctx.quadraticCurveTo(0, cardH, 0, cardH - radius);
                ctx.lineTo(0, radius);
                ctx.quadraticCurveTo(0, 0, radius, 0);
                ctx.closePath();
                ctx.fill();

                // draw QR code centered
                const qrX = (cardW - qrSize) / 2;
                const qrY = padTop;
                if (qrCanvas) ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);

                // draw title
                const titleY = qrY + qrSize + 48 * scale;
                ctx.fillStyle = '#3A2E22';
                ctx.font = `bold ${26 * scale}px Inter, sans-serif`;
                ctx.textAlign = 'center';
                ctx.fillText(coupon.title || '', cardW / 2, titleY);

                // draw description
                const descY = titleY + 32 * scale;
                ctx.fillStyle = '#7A6957';
                ctx.font = `${16 * scale}px Inter, sans-serif`;
                ctx.fillText(coupon.description || '', cardW / 2, descY);

                // draw discount badge
                const discountText = coupon.discount_type === 'percentage'
                    ? coupon.discount_value + '% OFF'
                    : '₱' + coupon.discount_value + ' OFF';
                const badgeY = descY + 40 * scale;
                const badgeH = 44 * scale;
                const badgeFont = `bold ${20 * scale}px Inter, sans-serif`;
                ctx.font = badgeFont;
                const badgeTextW = ctx.measureText(discountText).width;
                const badgePadX = 36 * scale;
                const badgeW = badgeTextW + badgePadX * 2;
                const badgeX = (cardW - badgeW) / 2;
                const badgeR = badgeH / 2;
                ctx.fillStyle = '#4A6741';
                ctx.beginPath();
                ctx.moveTo(badgeX + badgeR, badgeY);
                ctx.lineTo(badgeX + badgeW - badgeR, badgeY);
                ctx.quadraticCurveTo(badgeX + badgeW, badgeY, badgeX + badgeW, badgeY + badgeR);
                ctx.lineTo(badgeX + badgeW, badgeY + badgeH - badgeR);
                ctx.quadraticCurveTo(badgeX + badgeW, badgeY + badgeH, badgeX + badgeW - badgeR, badgeY + badgeH);
                ctx.lineTo(badgeX + badgeR, badgeY + badgeH);
                ctx.quadraticCurveTo(badgeX, badgeY + badgeH, badgeX, badgeY + badgeH - badgeR);
                ctx.lineTo(badgeX, badgeY + badgeR);
                ctx.quadraticCurveTo(badgeX, badgeY, badgeX + badgeR, badgeY);
                ctx.closePath();
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = badgeFont;
                ctx.fillText(discountText, cardW / 2, badgeY + badgeH / 2 + 7 * scale);

                // draw validity
                const validY = badgeY + badgeH + 24 * scale;
                ctx.fillStyle = '#9E8C78';
                ctx.font = `${14 * scale}px Inter, sans-serif`;
                ctx.fillText('Valid: ' + coupon.valid_from + ' – ' + coupon.valid_until, cardW / 2, validY);

                // draw divider
                const dividerY = validY + 32 * scale;
                ctx.strokeStyle = '#D6CFC3';
                ctx.lineWidth = 1 * scale;
                ctx.beginPath();
                ctx.moveTo(padX, dividerY);
                ctx.lineTo(cardW - padX, dividerY);
                ctx.stroke();

                // draw footer
                const footerY1 = dividerY + 28 * scale;
                ctx.fillStyle = '#9E8C78';
                ctx.font = `italic ${14 * scale}px Inter, sans-serif`;
                ctx.fillText('BrewHub • ' + city, cardW / 2, footerY1);

                const footerY2 = footerY1 + 24 * scale;
                ctx.fillStyle = '#3A2E22';
                ctx.font = `600 ${15 * scale}px Inter, sans-serif`;
                ctx.fillText(establishmentName, cardW / 2, footerY2);

                // download
                const link = document.createElement('a');
                link.download = (coupon.title || 'coupon').replace(/[^a-z0-9]/gi, '-') + '-QR.png';
                link.href = outCanvas.toDataURL('image/png');
                link.click();

                document.body.removeChild(tmp);
            }, 300);
        },
        rowMatches(status, title, description) {
            const active = (this.tableFilter || 'all').toLowerCase();
            const query = (this.tableSearch || '').toLowerCase().trim();
            const normalizedStatus = String(status || '').toLowerCase();
            const normalizedTitle = String(title || '').toLowerCase();
            const normalizedDescription = String(description || '').toLowerCase();

            const matchesFilter = active === 'all' || normalizedStatus === active;
            const matchesSearch = !query
                || normalizedTitle.includes(query)
                || normalizedDescription.includes(query)
                || normalizedStatus.includes(query);

            return matchesFilter && matchesSearch;
        },
        openScannerModal() {
            if (!this.isWebMobile) {
                return;
            }

            this.scannerError = '';
            this.scannerSuccess = '';
            this.scannerBusy = false;
            this.scannerModalOpen = true;
            this.$nextTick(() => this.startScanner());
        },
        async closeScannerModal() {
            this.scannerModalOpen = false;
            await this.stopScanner();
        },
        async startScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                this.scannerError = 'QR scanner is not available right now. Please refresh and try again.';
                return;
            }

            const targetId = 'coupon-owner-scanner';
            const targetElement = document.getElementById(targetId);
            if (!targetElement) {
                this.scannerError = 'Scanner view is not ready yet. Please try again.';
                return;
            }

            if (this.scannerInstance) {
                return;
            }

            this.scannerError = '';
            this.scannerSuccess = '';

            const scanner = new Html5Qrcode(targetId);
            this.scannerInstance = scanner;

            try {
                await scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 10,
                        qrbox: { width: 230, height: 230 },
                        aspectRatio: 1,
                    },
                    async (decodedText) => {
                        if (this.scannerBusy) {
                            return;
                        }

                        this.scannerBusy = true;
                        await this.redeemScannedQr(decodedText);
                        this.scannerBusy = false;
                    },
                    () => {
                        // Scanner read errors are expected while camera is searching.
                    }
                );
            } catch (error) {
                this.scannerError = 'Unable to access camera. Please allow camera permission and retry.';
                await this.stopScanner();
            }
        },
        async stopScanner() {
            if (!this.scannerInstance) {
                return;
            }

            const scanner = this.scannerInstance;
            this.scannerInstance = null;

            try {
                await scanner.stop();
            } catch (_error) {
                // Ignore stop errors if scanner is not running.
            }

            try {
                await scanner.clear();
            } catch (_error) {
                // Ignore clear errors.
            }
        },
        async redeemScannedQr(decodedText) {
            const qrData = String(decodedText || '').trim();
            if (!qrData) {
                this.scannerError = 'Scanned data is empty. Please try again.';
                return;
            }

            this.scannerError = '';
            this.scannerSuccess = '';

            try {
                const response = await fetch(this.redeemScanRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qr_data: qrData }),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload?.status !== 'success') {
                    this.scannerError = payload?.message || 'Unable to redeem this promo. Please try again.';
                    return;
                }

                this.scannerSuccess = payload?.message || 'Promo redeemed successfully.';
                await this.stopScanner();
                setTimeout(() => {
                    window.location.reload();
                }, 700);
            } catch (_error) {
                this.scannerError = 'Unable to redeem this promo right now. Please try again.';
            }
        }
    }"
    class="space-y-6 text-[#3A2E22]"
>
    <div class="coupon-promos-header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-display font-bold text-[#3A2E22] mb-1">
                Coupon <span class="italic text-[#4A6741]">Promo</span>
            </h1>
            <p class="text-[#9E8C78] text-sm font-medium">Manage your promo coupons and QR codes</p>
        </div>

        <div class="coupon-promos-header-actions flex items-center gap-2">
            <button
                type="button"
                x-on:click="openScannerModal()"
                :disabled="!isWebMobile"
                :class="!isWebMobile ? 'cursor-not-allowed opacity-55' : ''"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#4A6741] px-4 py-2 text-sm font-semibold text-[#4A6741] transition-colors hover:bg-[#F5F0E8]"
            >
                <svg x-show="isWebMobile" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h3m8 0h3a2 2 0 012 2v3m0 8a2 2 0 01-2 2h-3m-8 0H5a2 2 0 01-2-2v-3m5-5h8"/>
                </svg>
                <span x-show="isWebMobile" x-cloak>Scan QR</span>
                <span x-show="!isWebMobile" x-cloak>Scan QR on web mobile device</span>
            </button>

            <button
                type="button"
                x-on:click="openCreateModal()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#4A6741] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#3d5735]"
            >
                <span class="mr-1.5">+</span>
                Create New Coupon
            </button>
        </div>
    </div>

    <div class="coupon-promos-overview-grid grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-green-500 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Active Promos</p>
                    <p class="mt-2 text-3xl font-bold text-[#3A2E22]">{{ $activeCoupons }}</p>
                    <p class="text-xs text-[#9E8C78] mt-2">Currently redeemable offers</p>
                </div>
                <div class="rounded-full bg-green-100 p-3 text-green-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-red-400 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Expired Promos</p>
                    <p class="mt-2 text-3xl font-bold text-[#3A2E22]">{{ $expiredCoupons }}</p>
                    <p class="text-xs text-[#9E8C78] mt-2">No longer valid for claims</p>
                </div>
                <div class="rounded-full bg-red-100 p-3 text-red-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-[#E5DDD0] border-l-4 border-l-gray-400 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[#9E8C78] text-sm font-medium">Draft Promos</p>
                    <p class="mt-2 text-3xl font-bold text-[#3A2E22]">{{ $draftCoupons }}</p>
                    <p class="text-xs text-[#9E8C78] mt-2">Saved but not yet published</p>
                </div>
                <div class="rounded-full bg-gray-100 p-3 text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="coupon-promos-list-card filter-content bg-white rounded-xl shadow-sm p-8">
        <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-2">
            All <span class="italic text-[#4A6741]">Coupon Promos</span>
        </h2>
        <p class="text-[#9E8C78] text-sm mb-6">Complete list of your coupon promos</p>

        <div class="mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <button type="button" class="coupon-filter-tab px-4 py-2 text-sm font-medium transition-colors" x-on:click="tableFilter = 'all'" :style="tableFilter === 'all' ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F;' : 'color: #9E8C78; border-bottom: 3px solid transparent;'">All</button>
                <button type="button" class="coupon-filter-tab px-4 py-2 text-sm font-medium transition-colors" x-on:click="tableFilter = 'active'" :style="tableFilter === 'active' ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F;' : 'color: #9E8C78; border-bottom: 3px solid transparent;'">Active</button>
                <button type="button" class="coupon-filter-tab px-4 py-2 text-sm font-medium transition-colors" x-on:click="tableFilter = 'expired'" :style="tableFilter === 'expired' ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F;' : 'color: #9E8C78; border-bottom: 3px solid transparent;'">Expired</button>
                <button type="button" class="coupon-filter-tab px-4 py-2 text-sm font-medium transition-colors" x-on:click="tableFilter = 'draft'" :style="tableFilter === 'draft' ? 'color: #3B2F2F; border-bottom: 3px solid #3B2F2F;' : 'color: #9E8C78; border-bottom: 3px solid transparent;'">Draft</button>
            </div>

            <div class="relative w-full lg:w-auto">
                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model.debounce.150ms="tableSearch" placeholder="Search by title, description, or status..." class="w-full lg:w-72 pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4A6741] focus:border-transparent" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="background-color: #3B2F2F;">
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Promo Title</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Discount</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Valid Period</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Usage</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase text-white">Actions</th>
                    </tr>
                </thead>
                <tbody id="coupon-promos-tbody">
                    @forelse ($coupons as $coupon)
                        @php
                            $usagePercent = $coupon->max_usage > 0 ? min(100, round(($coupon->used_count / $coupon->max_usage) * 100)) : 0;
                            $status = strtolower($coupon->status);
                            $derivedStatus = $status;
                            if ($status === 'active' && \Illuminate\Support\Carbon::parse($coupon->valid_until)->isBefore(\Illuminate\Support\Carbon::today())) {
                                $derivedStatus = 'expired';
                            }
                        @endphp
                        <tr
                            class="coupon-row border-b border-gray-100 hover:bg-[#FAF7F2] transition-colors"
                            data-status="{{ $derivedStatus }}"
                            data-title="{{ strtolower($coupon->title) }}"
                            data-description="{{ strtolower($coupon->description) }}"
                            x-show="rowMatches('{{ $derivedStatus }}', @js($coupon->title), @js($coupon->description))"
                            x-cloak
                            style="background-color: {{ $loop->index % 2 === 1 ? '#FAF7F2' : '#FFFFFF' }};"
                        >
                            <td class="px-6 py-4 text-sm font-medium text-[#3A2E22]">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-sm align-top">
                                <p class="font-semibold text-[#3A2E22]">{{ $coupon->title }}</p>
                                <p class="mt-1 text-xs text-[#9E8C78]">{{ $coupon->description }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-[#3A2E22]">
                                @if ($coupon->discount_type === 'percentage')
                                    {{ rtrim(rtrim(number_format((float) $coupon->discount_value, 2, '.', ''), '0'), '.') }}%
                                @else
                                    PHP {{ number_format((float) $coupon->discount_value, 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-[#9E8C78]">
                                {{ \Illuminate\Support\Carbon::parse($coupon->valid_from)->format('M d, Y') }} - {{ \Illuminate\Support\Carbon::parse($coupon->valid_until)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm min-w-[180px]">
                                <p class="text-[#3A2E22]">{{ $coupon->used_count }} / {{ $coupon->max_usage }}</p>
                                <div class="mt-2 h-1.5 w-full rounded-full bg-[#E9E1D4]">
                                    <div class="h-1.5 rounded-full bg-green-500" style="width: {{ $usagePercent }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($derivedStatus === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(74, 103, 65, 0.15); color: #4A6741;">Active</span>
                                @elseif ($derivedStatus === 'expired')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(220, 38, 38, 0.12); color: #dc2626;">Expired</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background-color: rgba(107, 114, 128, 0.15); color: #4b5563;">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#F3EEE5] text-[#3A2E22] hover:bg-[#E8E0D2]"
                                        title="View QR Code"
                                        x-on:click="selectedCoupon = {
                                            id: {{ $coupon->id }},
                                            title: @js($coupon->title),
                                            description: @js($coupon->description),
                                            discount_type: @js($coupon->discount_type),
                                            discount_value: {{ (float) $coupon->discount_value }},
                                            valid_from: @js(\Illuminate\Support\Carbon::parse($coupon->valid_from)->format('M d, Y')),
                                            valid_until: @js(\Illuminate\Support\Carbon::parse($coupon->valid_until)->format('M d, Y')),
                                            max_usage: {{ (int) $coupon->max_usage }},
                                            used_count: {{ (int) $coupon->used_count }},
                                            status: @js($derivedStatus),
                                            qr_code_token: @js($coupon->qr_code_token),
                                            analytics: @js($coupon->analytics ?? [])
                                        }; setCurrentCoupon(selectedCoupon); qrModalOpen = true"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 4h2m-2-4h6m-4 0v6m-4-4h2" />
                                        </svg>
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#F3EEE5] text-[#3A2E22] hover:bg-[#E8E0D2]"
                                        title="Claim Analytics"
                                        x-on:click="selectedCoupon = {
                                            id: {{ $coupon->id }},
                                            title: @js($coupon->title),
                                            description: @js($coupon->description),
                                            discount_type: @js($coupon->discount_type),
                                            discount_value: {{ (float) $coupon->discount_value }},
                                            valid_from: @js(\Illuminate\Support\Carbon::parse($coupon->valid_from)->format('M d, Y')),
                                            valid_until: @js(\Illuminate\Support\Carbon::parse($coupon->valid_until)->format('M d, Y')),
                                            max_usage: {{ (int) $coupon->max_usage }},
                                            used_count: {{ (int) $coupon->used_count }},
                                            status: @js($derivedStatus),
                                            qr_code_token: @js($coupon->qr_code_token),
                                            analytics: @js($coupon->analytics ?? [])
                                        }; analyticsModalOpen = true; $nextTick(() => initAnalyticsCharts(selectedCoupon))"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18m-6-6v6m12-10v10m6-14v14" />
                                        </svg>
                                    </button>

                                    @if ($derivedStatus === 'draft')
                                        <button
                                            type="button"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#F3EEE5] text-[#3A2E22] hover:bg-[#E8E0D2]"
                                            title="Edit Coupon"
                                            x-on:click="openEditCoupon({
                                                id: {{ $coupon->id }},
                                                title: @js($coupon->title),
                                                description: @js($coupon->description),
                                                discount_type: @js($coupon->discount_type),
                                                discount_value: {{ (float) $coupon->discount_value }},
                                                valid_from: @js(
                                                    \Illuminate\Support\Carbon::parse($coupon->valid_from)->format('Y-m-d')
                                                ),
                                                valid_until: @js(
                                                    \Illuminate\Support\Carbon::parse($coupon->valid_until)->format('Y-m-d')
                                                ),
                                                max_usage: {{ (int) $coupon->max_usage }},
                                                status: 'draft'
                                            })"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100"
                                        title="Delete Coupon"
                                        x-on:click="selectedCoupon.id = {{ $coupon->id }}; selectedCoupon.title = @js($coupon->title); deleteModalOpen = true"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>

                                    <form id="delete-form-{{ $coupon->id }}" method="POST" action="{{ route('cafe-owner.coupon-promos.destroy', $coupon->id) }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-[#7A6957]">No coupons found for your cafe yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <template x-teleport="body">
        <div
            x-show="scannerModalOpen"
            x-transition.opacity
            class="coupon-scan-modal-shell fixed inset-0 z-[3200] flex items-center justify-center bg-black/45 px-4 py-6"
            x-on:click.self="closeScannerModal()"
            x-cloak
        >
            <div class="coupon-scan-modal w-full max-w-md rounded-2xl border border-[#E5DDD0] bg-white p-4 shadow-2xl">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-display font-bold text-[#3A2E22]">Scan Consumer QR</h3>
                        <p class="text-xs text-[#9E8C78] mt-0.5">In-store redemption for this cafe's promo</p>
                    </div>
                    <button type="button" x-on:click="closeScannerModal()" class="text-[#6A5A48] hover:text-[#3A2E22]">✕</button>
                </div>

                <div id="coupon-owner-scanner" class="coupon-scan-camera-wrap rounded-xl overflow-hidden border border-[#E5DDD0] bg-[#F8F4ED]"></div>

                <p class="mt-3 text-[11px] text-[#9E8C78]">Make sure the consumer opens the coupon QR from the mobile app before scanning.</p>

                <p x-show="scannerBusy" x-cloak class="mt-2 text-sm font-semibold text-[#4A6741]">Redeeming scanned promo...</p>
                <p x-show="scannerError" x-cloak x-text="scannerError" class="mt-2 text-sm text-red-600"></p>
                <p x-show="scannerSuccess" x-cloak x-text="scannerSuccess" class="mt-2 text-sm text-green-700"></p>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="createModalOpen"
            x-transition.opacity
            class="coupon-create-modal-shell fixed inset-0 z-[3000] flex items-center justify-center bg-black/40 px-4 py-6"
            x-on:click.self="createModalOpen = false"
            x-cloak
        >
        <div class="coupon-create-modal thin-modal-scrollbar w-full max-w-4xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-xl border border-[#E5DDD0] p-6 sm:p-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-display font-bold text-[#3A2E22]">Create Coupon</h2>
                <button type="button" x-on:click="createModalOpen = false" class="text-[#6A5A48] hover:text-[#3A2E22]">✕</button>
            </div>

            <form method="POST" x-bind:action="createForm.isEditing ? updateRouteBase + '/' + createForm.id : postRoute" class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                @csrf
                <input type="hidden" name="_method" x-bind:value="createForm.isEditing ? 'PATCH' : 'POST'" />
                <input type="hidden" name="status" x-model="createForm.status" />
                <div class="lg:col-span-3 space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Promo Title*</label>
                        <input
                            type="text"
                            name="title"
                            x-model="createForm.title"
                            x-on:input="renderCreateQr()"
                            placeholder="e.g., 20% Off Premium Barako Beans"
                            required
                            class="w-full rounded-lg border border-[#D8CFC1] px-3 py-2 text-sm focus:border-[#4A6741] focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Description*</label>
                        <textarea
                            name="description"
                            x-model="createForm.description"
                            rows="3"
                            placeholder="Brief description of the promo offer"
                            required
                            class="w-full rounded-lg border border-[#D8CFC1] px-3 py-2 text-sm focus:border-[#4A6741] focus:outline-none"
                        ></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Discount Type*</label>
                        <input type="hidden" name="discount_type" :value="createForm.discount_type" />
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <button
                                type="button"
                                x-on:click="createForm.discount_type = 'percentage'"
                                :class="createForm.discount_type === 'percentage' ? 'bg-[#4A6741] text-white border-[#4A6741]' : 'bg-white text-[#4A6741] border-[#4A6741]'"
                                class="rounded-lg border px-4 py-2 text-sm font-semibold transition-colors"
                            >
                                % Percentage
                            </button>
                            <button
                                type="button"
                                x-on:click="createForm.discount_type = 'fixed'"
                                :class="createForm.discount_type === 'fixed' ? 'bg-[#4A6741] text-white border-[#4A6741]' : 'bg-white text-[#4A6741] border-[#4A6741]'"
                                class="rounded-lg border px-4 py-2 text-sm font-semibold transition-colors"
                            >
                                $ Fixed Amount
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Discount Value*</label>
                        <div class="relative">
                            <input
                                type="number"
                                name="discount_value"
                                x-model="createForm.discount_value"
                                min="0"
                                step="0.01"
                                required
                                class="w-full rounded-lg border border-[#D8CFC1] px-3 py-2 pr-10 text-sm focus:border-[#4A6741] focus:outline-none"
                            />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-[#6A5A48]" x-text="createForm.discount_type === 'percentage' ? '%' : 'PHP'"></span>
                        </div>
                    </div>

                    <div class="coupon-date-grid grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Valid From*</label>
                            <input
                                type="date"
                                name="valid_from"
                                x-model="createForm.valid_from"
                                required
                                class="coupon-date-input w-full rounded-lg border border-[#D8CFC1] px-3 py-2 text-sm focus:border-[#4A6741] focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Valid To*</label>
                            <input
                                type="date"
                                name="valid_until"
                                x-model="createForm.valid_until"
                                required
                                class="coupon-date-input w-full rounded-lg border border-[#D8CFC1] px-3 py-2 text-sm focus:border-[#4A6741] focus:outline-none"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">Usage Limit (Max Claims)*</label>
                        <input
                            type="number"
                            name="max_usage"
                            x-model="createForm.max_usage"
                            min="1"
                            placeholder="e.g., 100"
                            required
                            class="w-full rounded-lg border border-[#D8CFC1] px-3 py-2 text-sm focus:border-[#4A6741] focus:outline-none"
                        />
                        <p class="mt-1 text-xs text-[#7A6957]">Maximum number of times this coupon can be claimed</p>
                    </div>

                    <!-- Status is set by the button action and is hidden from the create form -->

                    <div class="coupon-create-actions flex flex-wrap justify-end gap-2 pt-2">
                        <button type="submit"
                            x-on:click="createForm.status = 'draft'"
                            class="rounded-lg border border-[#D8CFC1] px-4 py-2 text-sm font-semibold text-[#3A2E22] hover:bg-[#F5F0E8]">
                            Save Draft
                        </button>
                        <button type="submit"
                            x-on:click="createForm.status = 'active'"
                            class="rounded-lg bg-[#4A6741] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3d5735]"
                            x-text="createForm.isEditing ? 'Update & Publish Coupon' : 'Create & Publish Coupon'"
                        >
                            Create &amp; Publish Coupon
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-[#F5F0E8] rounded-xl p-6 border border-[#E5DDD0]">
                        <h3 class="text-lg font-display font-semibold text-[#3A2E22]">QR Code Preview</h3>

                        <div class="mt-4 rounded-xl border border-dashed border-[#D8CFC1] bg-[#FAF7F1] p-5 text-center">
                            <div id="qr-preview" class="mx-auto flex min-h-[180px] min-w-[180px] items-center justify-center"></div>
                        </div>

                        <p class="mt-4 text-sm font-semibold text-[#3A2E22]" x-text="createForm.title || 'Your promo title will appear here'" ></p>
                        <p class="mt-1 text-xs text-[#6A5A48]" x-text="createForm.description || 'Brief description preview'" ></p>
                        <p class="mt-3 text-xs text-[#7A6957]">This QR code will be generated after creating the coupon. You can download and print it for display.</p>
                    </div>
                </div>
            </form>
        </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="qrModalOpen"
            x-transition.opacity
            class="coupon-qr-modal-shell fixed inset-0 z-[3000] flex items-center justify-center bg-black/40 px-4"
            x-on:click.self="qrModalOpen = false"
            x-cloak
        >
            <div class="coupon-qr-modal thin-modal-scrollbar w-full max-w-6xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-xl border border-[#E5DDD0]">
                <!-- Header with buttons -->
                <div class="coupon-qr-header p-6 border-b border-[#E5DDD0]">
                    <div class="coupon-qr-header-top flex items-center justify-between gap-3">
                        <h2 class="text-2xl font-display font-bold text-[#3A2E22]">View QR Code</h2>
                        <button type="button" x-on:click="qrModalOpen = false" class="coupon-qr-close-btn text-[#6A5A48] hover:text-[#3A2E22]">✕</button>
                    </div>
                    <div class="coupon-qr-header-actions mt-3 flex items-center gap-3">
                        <button
                            type="button"
                            onclick="printCoupon()"
                            class="flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-[#D8CFC1] px-4 py-2 text-sm font-semibold text-[#3A2E22] hover:bg-[#F5F0E8] transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                        <button
                            type="button"
                            x-on:click="downloadQRCode()"
                            class="flex items-center justify-center gap-2 whitespace-nowrap rounded-lg bg-[#4A6741] px-4 py-2 text-sm font-semibold text-white hover:bg-[#3d5735] transition-colors"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download QR Code
                        </button>
                    </div>
                </div>

                <!-- Main content -->
                <div class="coupon-qr-content flex min-h-[600px]">
                    <!-- Left column (60%) -->
                    <div class="coupon-qr-main w-3/5 p-6 border-r border-[#E5DDD0]">
                        <!-- QR Code -->
                        <div class="text-center mb-6">
                            <div id="qr-display" class="inline-block"></div>
                        </div>

                        <!-- Coupon details -->
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-display font-bold text-[#3A2E22] mb-2" x-text="selectedCoupon.title"></h3>
                            <p class="text-sm text-[#9E8C78]" x-text="selectedCoupon.description"></p>
                        </div>

                        <!-- Discount badge -->
                        <div class="text-center mb-6">
                            <span
                                class="inline-block rounded-full bg-[#4A6741] px-8 py-3 text-xl font-bold text-white"
                                x-text="selectedCoupon.discount_type === 'percentage' ? (selectedCoupon.discount_value + '% OFF') : ('PHP ' + selectedCoupon.discount_value + ' OFF')"
                            ></span>
                        </div>

                        <!-- Valid period -->
                        <div class="flex items-center justify-center gap-2 mb-4 text-sm text-[#3A2E22]">
                            <svg class="h-4 w-4 text-[#9E8C78]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Valid: <span x-text="selectedCoupon.valid_from"></span> - <span x-text="selectedCoupon.valid_until"></span></span>
                        </div>

                        <!-- Available claims -->
                        <div class="text-center mb-6 print-hidden">
                            <p class="text-sm text-[#9E8C78]">Available Claims</p>
                            <p class="text-2xl font-bold text-[#3A2E22]" x-text="Math.max(0, (selectedCoupon.max_usage || 0) - (selectedCoupon.used_count || 0))"></p>
                        </div>

                        <!-- How to claim -->
                        <div class="bg-[#F5F0E8] rounded-lg p-4 print-hidden">
                            <h4 class="font-semibold text-[#3A2E22] mb-2">How to Claim</h4>
                            <p class="text-sm text-[#9E8C78]">Customers can scan this QR code using their mobile device to redeem this coupon at your establishment.</p>
                        </div>
                    </div>

                    <!-- Right column (40%) -->
                    <div class="coupon-qr-side w-2/5 p-6 print-hidden">
                        <!-- Display Tips Card -->
                        <div class="coupon-qr-tips bg-white rounded-xl border border-[#E5DDD0] p-6 print-hidden">
                            <h4 class="text-lg font-display font-bold text-[#3A2E22] mb-4 flex items-center gap-2">
                                <span>💡</span> Display Tips
                            </h4>

                            <ul class="coupon-qr-tips-list space-y-3 text-sm text-[#9E8C78]">
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A6741] mt-1">•</span>
                                    <span>Print on high-quality paper for better scanning</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A6741] mt-1">•</span>
                                    <span>Display at eye level for maximum visibility</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A6741] mt-1">•</span>
                                    <span>Keep it visible at entrance or checkout area</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A6741] mt-1">•</span>
                                    <span>Laminate for durability and weather protection</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-[#4A6741] mt-1">•</span>
                                    <span>Update display when promo period ends</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-[#E5DDD0] p-4 text-center">
                    <p class="text-sm text-[#9E8C78] italic">BrewHub &bull; {{ $establishment->city ?? 'Lipa' }}</p>
                    <p class="text-sm font-semibold text-[#3A2E22] mt-0.5">{{ $establishment->name ?? 'BrewHub Cafe' }}</p>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            x-show="analyticsModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-[3000] flex items-center justify-center bg-black/40 px-4"
            x-on:click.self="analyticsModalOpen = false"
            x-cloak
        >
            <div class="thin-modal-scrollbar w-full max-w-4xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl shadow-xl border border-[#E5DDD0] p-6 sm:p-8">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-display font-bold">Claim Analytics</h2>
                <div class="flex items-center gap-2">
                    <button type="button" x-on:click="downloadAnalyticsReport()" title="Download Analytics Report" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#E5DDD0] text-[#6A5A48] hover:bg-[#FAF7F1] hover:text-[#3A2E22] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/></svg>
                    </button>
                    <button type="button" x-on:click="analyticsModalOpen = false" class="text-[#6A5A48] hover:text-[#3A2E22]">✕</button>
                </div>
            </div>

            <p class="text-sm font-semibold text-[#3A2E22]" x-text="selectedCoupon.title"></p>

            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                <div class="rounded-xl border border-[#E5DDD0] bg-[#FAF7F1] p-4">
                    <p class="text-xs text-[#7A6957]">Total Claims</p>
                    <p class="mt-1 text-3xl font-bold text-[#3A2E22]" x-text="selectedCoupon.used_count"></p>
                </div>
                <div class="rounded-xl border border-[#E5DDD0] bg-[#FAF7F1] p-4">
                    <p class="text-xs text-[#7A6957]">Remaining Claims</p>
                    <p class="mt-1 text-3xl font-bold text-[#3A2E22]" x-text="Math.max(0, (selectedCoupon.max_usage || 0) - (selectedCoupon.used_count || 0))"></p>
                </div>
                <div class="rounded-xl border border-[#E5DDD0] bg-[#FAF7F1] p-4">
                    <p class="text-xs text-[#7A6957]">Claim Rate</p>
                    <span class="mt-2 inline-flex rounded-full bg-[#4A6741] px-3 py-1 text-sm font-semibold text-white" x-text="(selectedCoupon.max_usage || 0) > 0 ? Math.round(((selectedCoupon.used_count || 0) / selectedCoupon.max_usage) * 100) + '%' : '0%'"></span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-[#E5DDD0] bg-white p-4">
                    <h3 class="text-sm font-semibold text-[#3A2E22]">Daily Claims Breakdown</h3>
                    <div class="mt-3 h-64">
                        <canvas id="daily-claims-chart"></canvas>
                    </div>
                </div>

                <div class="rounded-xl border border-[#E5DDD0] bg-white p-4">
                    <h3 class="text-sm font-semibold text-[#3A2E22]">Claims by Time of Day</h3>
                    <div class="mt-3 h-64">
                        <canvas id="time-of-day-chart"></canvas>
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-[#E5DDD0] bg-white p-4">
                <h3 class="text-sm font-semibold text-[#3A2E22]">Recent Claims</h3>
                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5DDD0]">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-[#9E8C78]">
                                <th class="pb-2 pr-4">Date &amp; Time</th>
                                <th class="pb-2 px-4">Customer</th>
                                <th class="pb-2 px-4">Location</th>
                                <th class="pb-2 px-4">Discount Applied</th>
                                <th class="pb-2 pl-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F0E9DE]">
                            <template x-for="(row, idx) in analyticsRows" :key="idx">
                                <tr>
                                    <td class="py-2 pr-4 text-sm text-[#3A2E22]" x-text="row.dateTime"></td>
                                    <td class="py-2 px-4 text-sm text-[#3A2E22]" x-text="row.customer"></td>
                                    <td class="py-2 px-4 text-sm text-[#3A2E22]" x-text="row.location"></td>
                                    <td class="py-2 px-4 text-sm text-[#3A2E22]" x-text="row.discount"></td>
                                    <td class="py-2 pl-4">
                                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700" x-text="row.status"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="analyticsRows.length === 0">
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-[#7A6957]">No claims recorded yet.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div
            class="fixed inset-0 z-[3000] flex items-center justify-center px-4"
            x-show="deleteModalOpen"
            @keydown.escape="deleteModalOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-cloak
            style="display: none;"
        >
            <div class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click.stop="deleteModalOpen = false"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full" @click.stop>
                <div class="p-6">
                    <h2 class="text-2xl font-display font-bold text-[#3A2E22] mb-4">
                        Delete Coupon?
                    </h2>
                    <p class="text-[#3A2E22] mb-6">
                        Are you sure you want to delete <span class="font-semibold" x-text="selectedCoupon.title"></span>? This action cannot be undone.
                    </p>
                    <div class="flex gap-3">
                        <button
                            type="button"
                            @click="deleteModalOpen = false"
                            class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700 transition-colors"
                            x-on:click="const form = document.getElementById(`delete-form-${selectedCoupon.id}`); if (form) form.submit();"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection

@section('print')
<div id="print-area" class="hidden">
  <div style="
    width: 100%;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    font-family: 'Inter', sans-serif;
  ">
    <div style="
      width: 580px;
      text-align: center;
      padding: 64px 56px 56px;
      border-radius: 20px;
      background: #F0EBE1;
    ">
      <!-- QR Code - centered -->
      <div id="print-qr-code" style="
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto 48px auto;
      "></div>

      <!-- Coupon Title -->
      <h2 id="print-title" style="
        font-size: 26px;
        font-weight: 700;
        color: #3A2E22;
        margin-bottom: 10px;
      "></h2>

      <!-- Description -->
      <p id="print-description" style="
        font-size: 16px;
        color: #7A6957;
        margin-bottom: 20px;
      "></p>

      <!-- Discount Badge -->
      <div id="print-discount" style="
        display: inline-block;
        background: #4A6741;
        color: #ffffff;
        font-size: 20px;
        font-weight: 700;
        padding: 10px 36px;
        border-radius: 999px;
        margin-bottom: 16px;
      "></div>

      <!-- Valid Period -->
      <p id="print-validity" style="
        font-size: 14px;
        color: #9E8C78;
        margin-bottom: 32px;
      "></p>

      <!-- Divider -->
      <hr style="border: none; border-top: 1px solid #D6CFC3; margin-bottom: 20px;">

      <!-- Branding Footer -->
      <p style="font-size: 14px; color: #9E8C78; font-style: italic; margin-bottom: 2px;">
        BrewHub &bull; <span id="print-city"></span>
      </p>
      <p id="print-establishment-name" style="
        font-size: 15px;
        font-weight: 600;
        color: #3A2E22;
        margin-top: 2px;
      "></p>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
    .thin-modal-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #c8beb0 transparent;
    }

    .thin-modal-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .thin-modal-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .thin-modal-scrollbar::-webkit-scrollbar-thumb {
        background-color: #c8beb0;
        border-radius: 9999px;
    }

    .thin-modal-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: #b9ad9d;
    }

    @media (min-width: 768px) {
        .coupon-promos-header {
            margin-bottom: 0.85rem;
        }

        .coupon-promos-overview-grid {
            margin-top: 0;
            margin-bottom: 1.25rem;
            gap: 1rem;
        }

        .coupon-promos-list-card {
            margin-top: 0;
        }

        .coupon-qr-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .coupon-qr-header-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }
    }

    @media (max-width: 767px) {
        .cafe-coupon-promos-page .coupon-promos-header {
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .cafe-coupon-promos-page .coupon-promos-header-actions {
            width: 100%;
            justify-content: stretch;
            flex-wrap: nowrap;
            gap: 0.45rem;
        }

        .cafe-coupon-promos-page .coupon-promos-header-actions > button {
            flex: 1 1 0;
            white-space: nowrap;
            width: 50%;
            min-width: 0;
            padding: 0.58rem 0.68rem;
            font-size: 0.72rem;
        }

        .coupon-scan-modal-shell {
            align-items: flex-start;
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
        }

        .coupon-scan-modal {
            max-height: 92dvh;
            overflow-y: auto;
        }

        .coupon-scan-camera-wrap {
            min-height: 260px;
        }

        .cafe-coupon-promos-page .coupon-promos-overview-grid {
            margin-top: 0.35rem;
            margin-bottom: 1.1rem;
        }

        .cafe-coupon-promos-page .coupon-promos-list-card {
            margin-top: 0.35rem;
        }

        .cafe-coupon-promos-page .flex.items-center.justify-between {
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .cafe-coupon-promos-page .text-3xl {
            font-size: 1.7rem !important;
            line-height: 2rem;
        }

        .cafe-coupon-promos-page .grid.grid-cols-1.md\:grid-cols-2.xl\:grid-cols-4,
        .cafe-coupon-promos-page .grid.grid-cols-1.lg\:grid-cols-3 {
            gap: 0.9rem !important;
        }

        .cafe-coupon-promos-page .bg-white.rounded-2xl.shadow-sm.border.border-\[\#E5DDD0\].p-6,
        .cafe-coupon-promos-page .bg-white.rounded-2xl.shadow-sm.border.border-\[\#E5DDD0\].p-5,
        .cafe-coupon-promos-page .bg-white.rounded-2xl.shadow-sm.border.border-\[\#E5DDD0\].p-4 {
            padding: 1rem !important;
        }

        .coupon-create-modal-shell {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            align-items: flex-start;
            overflow-x: hidden;
        }

        .coupon-create-modal {
            width: min(42rem, calc(100vw - 0.75rem));
            max-width: calc(100vw - 0.75rem);
            max-height: 90dvh;
            padding: 1rem !important;
            overflow-x: hidden;
        }

        .coupon-create-modal form {
            gap: 1rem;
            min-width: 0;
        }

        .coupon-create-modal * {
            min-width: 0;
        }

        .coupon-create-modal .lg\:col-span-3,
        .coupon-create-modal .lg\:col-span-2,
        .coupon-create-modal .space-y-4,
        .coupon-create-modal .space-y-4 > div {
            min-width: 0;
        }

        .coupon-date-grid {
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 0.75rem !important;
        }

        .coupon-date-grid > div {
            min-width: 0;
            overflow: hidden;
        }

        .coupon-date-input {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            inline-size: 100% !important;
            min-inline-size: 0 !important;
            max-inline-size: 100% !important;
            display: block;
            box-sizing: border-box;
            font-size: 16px;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            -webkit-appearance: none;
            appearance: none;
            padding-right: 2rem;
        }

        .coupon-date-input::-webkit-date-and-time-value {
            text-align: left;
            min-width: 0;
            width: 100%;
        }

        .coupon-date-input::-webkit-datetime-edit,
        .coupon-date-input::-webkit-datetime-edit-fields-wrapper {
            min-width: 0;
            width: 100%;
            padding: 0;
        }

        .coupon-date-input::-webkit-calendar-picker-indicator {
            margin: 0;
            opacity: 1;
        }

        .coupon-create-modal input,
        .coupon-create-modal textarea,
        .coupon-create-modal select,
        .coupon-create-modal button {
            max-width: 100%;
            box-sizing: border-box;
        }

        .coupon-create-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .coupon-create-actions > button {
            width: 100%;
            justify-content: center;
        }

        .coupon-qr-modal-shell {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            align-items: flex-start;
        }

        .coupon-qr-modal {
            max-height: 90dvh;
        }

        .coupon-qr-header {
            padding: 1rem;
        }

        .coupon-qr-header-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .coupon-qr-header h2 {
            font-size: 1.55rem;
            line-height: 1.85rem;
        }

        .coupon-qr-close-btn {
            flex-shrink: 0;
            min-height: 2rem;
            min-width: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .coupon-qr-header-actions {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            align-items: center;
            margin-top: 0.7rem;
        }

        .coupon-qr-header-actions > button {
            min-height: 2.5rem;
            justify-content: center;
            margin-left: 0 !important;
            white-space: nowrap;
            font-size: 0.78rem;
            padding-left: 0.65rem;
            padding-right: 0.65rem;
        }

        .coupon-qr-content {
            flex-direction: column;
            min-height: 0 !important;
        }

        .coupon-qr-main,
        .coupon-qr-side {
            width: 100% !important;
            padding: 1rem;
        }

        .coupon-qr-main {
            border-right: 0 !important;
            border-bottom: 1px solid #E5DDD0;
        }

        #qr-display canvas,
        #qr-display img {
            width: min(72vw, 240px) !important;
            height: auto !important;
        }

        .coupon-qr-tips {
            padding: 0.9rem;
        }

        .coupon-qr-tips-list {
            display: grid;
            gap: 0.45rem;
        }

        .coupon-qr-tips-list li:nth-child(n + 4) {
            display: none;
        }

        .coupon-qr-modal > .border-t.border-\[\#E5DDD0\].p-4 {
            padding: 0.75rem !important;
        }
    }

    @media print {
        body > *:not(#print-area) {
            display: none !important;
        }

        #print-area {
            display: flex !important;
            width: 100%;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let currentCoupon = {};

    function setCurrentCoupon(coupon) {
        currentCoupon = { ...(coupon || {}) };
    }

    function printCoupon() {
        if (!currentCoupon || !currentCoupon.qr_code_token) {
            return;
        }

        document.getElementById('print-qr-code').innerHTML = '';
        document.getElementById('print-title').textContent = currentCoupon.title || '';
        document.getElementById('print-description').textContent = currentCoupon.description || '';
        document.getElementById('print-discount').textContent = currentCoupon.discount_type === 'percentage'
            ? currentCoupon.discount_value + '% OFF'
            : '₱' + currentCoupon.discount_value + ' OFF';
        document.getElementById('print-validity').textContent = 'Valid: ' + currentCoupon.valid_from + ' – ' + currentCoupon.valid_until;
        document.getElementById('print-city').textContent = currentCoupon.city ?? @js($establishment->city ?? 'Lipa');
        document.getElementById('print-establishment-name').textContent = @js($establishment->name ?? 'BrewHub Cafe');

        new QRCode(document.getElementById('print-qr-code'), {
            text: currentCoupon.qr_code_token,
            width: 340,
            height: 340,
            colorDark: '#3A2E22',
            colorLight: '#F0EBE1'
        });

        setTimeout(() => { window.print(); }, 300);
    }
</script>
@endpush
