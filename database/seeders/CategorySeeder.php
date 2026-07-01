<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private const COLORS = [
        '#3B82F6',
        '#10B981',
        '#F59E0B',
        '#EF4444',
        '#8B5CF6',
        '#06B6D4',
        '#84CC16',
        '#F97316',
    ];

    private const CATEGORIES = [
        'الصودا',
        'السموزي',
        'المنعنع',
        'الافوكادو',
        'الايس كريم',
        'عالم القصب',
        'عالم السوبيا',
        'عصائر شرقية',
        'عصائر الفيتامينات',
        'الميلك تشيك',
        'القشطوطة',
        'طواجن و حلو الصعيدي',
        'كشري الحلو',
        'رز بلبن',
        'فخفخينا الحلو',
        'تلاجة الصعيدي',
        'مشروبات ساخنة',
        'وافل / فريسكا',
        'ركن المكسرات',
        'الشوكولاته',
        'الرضعات',
        'الزبادي',
        'عالم المانجو',
        'عصائر فريش',
        'عبوات عائلية',
    ];

    /**
     * Seed default product categories.
     */
    public function run(): void
    {
        $created = 0;
        $updated = 0;

        foreach (self::CATEGORIES as $index => $name) {
            $color = self::COLORS[$index % count(self::COLORS)];

            $category = Category::updateOrCreate(
                ['name' => $name],
                ['color' => $color],
            );

            $category->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Categories seeded: {$created} created, {$updated} updated.");
    }
}
