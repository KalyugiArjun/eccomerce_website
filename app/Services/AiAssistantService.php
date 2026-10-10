<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class AiAssistantService
{
    /**
     * Process conversational shopping query and match against real database items
     */
    public function ask(string $message, array $conversationHistory = []): array
    {
        $parsed = $this->parseIntent($message);
        
        $query = Product::with(['seller', 'category'])
            ->where('status', 'published')
            ->where('stock', '>', 0);

        // Apply price constraints
        if ($parsed['max_price']) {
            $query->where(function ($q) use ($parsed) {
                $q->where(function ($sub) use ($parsed) {
                    $sub->whereNotNull('discount_price')
                        ->where('discount_price', '<=', $parsed['max_price']);
                })->orWhere(function ($sub) use ($parsed) {
                    $sub->whereNull('discount_price')
                        ->where('price', '<=', $parsed['max_price']);
                });
            });
        }

        if ($parsed['min_price']) {
            $query->where(function ($q) use ($parsed) {
                $q->where(function ($sub) use ($parsed) {
                    $sub->whereNotNull('discount_price')
                        ->where('discount_price', '>=', $parsed['min_price']);
                })->orWhere(function ($sub) use ($parsed) {
                    $sub->whereNull('discount_price')
                        ->where('price', '>=', $parsed['min_price']);
                });
            });
        }

        // Apply keyword / category / color matching
        if (!empty($parsed['keywords'])) {
            $query->where(function ($q) use ($parsed) {
                foreach ($parsed['keywords'] as $kw) {
                    $q->orWhere('name', 'LIKE', "%{$kw}%")
                      ->orWhere('short_description', 'LIKE', "%{$kw}%")
                      ->orWhere('description', 'LIKE', "%{$kw}%")
                      ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', "%{$kw}%"))
                      ->orWhereHas('variants', fn($v) => $v->where('color', 'LIKE', "%{$kw}%"));
                }
            });
        }

        $products = $query->orderBy('rating', 'desc')
            ->take(6)
            ->get();

        // Build helpful natural language response
        $reply = $this->generateReplyText($message, $parsed, $products);

        return [
            'reply' => $reply,
            'products' => $products,
            'intent' => $parsed,
        ];
    }

    /**
     * Extract intent, price bounds, color, and keywords from natural language / Hinglish
     */
    protected function parseIntent(string $text): array
    {
        $lower = strtolower($text);
        $maxPrice = null;
        $minPrice = null;
        $keywords = [];

        // Match price patterns e.g. "under 2000", "₹2000 ke andar", "< 2500", "below 1500", "under ₹500"
        if (preg_match('/(?:under|below|less than|ke andar|se kam|within)\s*(?:₹|rs\.?|inr)?\s*([0-9]+)/i', $lower, $matches)) {
            $maxPrice = (float) $matches[1];
        } elseif (preg_match('/(?:₹|rs\.?)\s*([0-9]+)\s*(?:ke andar|se kam|or less|under)/i', $lower, $matches)) {
            $maxPrice = (float) $matches[1];
        }

        // Common Indian e-commerce terms & colors
        $colors = ['black', 'white', 'blue', 'red', 'green', 'yellow', 'pink', 'grey', 'brown', 'navy'];
        foreach ($colors as $color) {
            if (str_contains($lower, $color)) {
                $keywords[] = $color;
            }
        }

        // Product categories / keywords
        $catalogTerms = [
            'shoes', 'sneakers', 'boots', 'sandals', 'slippers',
            'watch', 'smartwatch', 'headphones', 'earbuds', 'neckband',
            'shirt', 't-shirt', 'tshirt', 'kurta', 'saree', 'jeans', 'trousers',
            'mobile', 'phone', 'laptop', 'tablet', 'speaker', 'tv',
            'cricket', 'bat', 'ball', 'gym', 'college', 'office', 'casual'
        ];

        foreach ($catalogTerms as $term) {
            if (str_contains($lower, $term)) {
                $keywords[] = $term;
            }
        }

        // Clean out stop words for residual keywords
        $stopWords = ['mujhe', 'chahiye', 'dikhao', 'aur', 'mein', 'ke', 'liye', 'kuch', 'batao', 'best', 'good', 'i', 'want', 'need', 'show', 'me', 'some', 'the', 'for', 'in', 'please'];
        $words = preg_split('/\s+/', preg_replace('/[^\w\s]/', '', $lower));
        foreach ($words as $word) {
            if (strlen($word) > 2 && !in_array($word, $stopWords) && !in_array($word, $keywords) && !is_numeric($word)) {
                $keywords[] = $word;
            }
        }

        return [
            'max_price' => $maxPrice,
            'min_price' => $minPrice,
            'keywords' => array_slice(array_unique($keywords), 0, 5),
        ];
    }

    protected function generateReplyText(string $userMsg, array $parsed, Collection $products): string
    {
        $count = $products->count();

        if ($count === 0) {
            $constraintText = $parsed['max_price'] ? " under ₹" . number_format($parsed['max_price']) : "";
            return "Maine ShopKart database mein check kiya, lekin aapki requirement {$constraintText} ke anusaar koi product abhi available nahi hai. Aap price range badha kar ya category badal kar try kar sakte hain!";
        }

        $reply = "Maine aapke liye ShopKart par {$count} genuine products dhundhe hain";
        if ($parsed['max_price']) {
            $reply .= " (budget: ₹" . number_format($parsed['max_price']) . " ke andar)";
        }
        $reply .= ". Inme se aap fast delivery aur verified seller ratings compare kar sakte hain:";

        return $reply;
    }
}
