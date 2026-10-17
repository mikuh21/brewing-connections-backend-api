-- Run this in the Supabase SQL Editor if the Laravel migration has not run yet.
-- Only users who own a roaster establishment are changed; cafe-owner accounts remain cafe_owner.
BEGIN;

UPDATE public.users AS u
SET role = 'roaster',
    updated_at = NOW()
WHERE u.role = 'cafe_owner'
  AND EXISTS (
      SELECT 1
      FROM public.establishments AS e
      WHERE e.type = 'roaster'
        AND e.owner_id = u.id
  );

COMMIT;

-- Verify the role counts after the update.
SELECT role, COUNT(*) AS account_count
FROM public.users
GROUP BY role
ORDER BY role;
