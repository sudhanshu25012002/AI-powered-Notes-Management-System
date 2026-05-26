<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Seed 10 diverse sample notes.
     * These notes are intentionally varied in topic so semantic search
     * returns meaningfully different results for different queries.
     * Embeddings are null — they will be generated when the seeder runs
     * via the GenerateEmbeddingJob (or manually after setup).
     */
    public function run(): void
    {
        $notes = [
            [
                'title'   => 'Getting Started with Machine Learning',
                'content' => 'Machine learning is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed. Key concepts include supervised learning, unsupervised learning, and reinforcement learning. Popular frameworks include TensorFlow, PyTorch, and scikit-learn. Start with linear regression before moving to neural networks.',
                'tags'    => ['machine-learning', 'ai', 'tech'],
            ],
            [
                'title'   => 'Healthy Mediterranean Diet Guide',
                'content' => 'The Mediterranean diet emphasizes fruits, vegetables, whole grains, legumes, nuts, and olive oil. Fish and seafood should be eaten twice a week. Red meat is consumed only occasionally. Studies show this diet reduces heart disease risk by up to 30%. Key foods include tomatoes, olives, feta cheese, chickpeas, and fresh herbs.',
                'tags'    => ['health', 'diet', 'nutrition'],
            ],
            [
                'title'   => 'Project Meeting Notes — Q2 Planning',
                'content' => 'Attended by: Sarah, Mike, James, and Lisa. Key decisions: 1) Launch new product feature by June 15th. 2) Increase marketing budget by 20%. 3) Hire 3 new engineers by end of Q2. Action items: Sarah to prepare product roadmap, Mike to finalize budget proposal, James to post job listings. Next meeting: Friday 3pm.',
                'tags'    => ['meeting', 'work', 'planning'],
            ],
            [
                'title'   => 'Tokyo Travel Itinerary — 7 Days',
                'content' => 'Day 1: Arrive Narita, check into hotel in Shinjuku. Day 2: Explore Asakusa temple, Akihabara electronics district. Day 3: Day trip to Nikko shrines. Day 4: Shibuya crossing, Harajuku fashion street, Meiji Shrine. Day 5: teamLab digital art museum, Odaiba waterfront. Day 6: Tsukiji fish market, Ginza shopping. Day 7: Depart from Haneda airport.',
                'tags'    => ['travel', 'japan', 'itinerary'],
            ],
            [
                'title'   => 'Docker and Kubernetes Overview',
                'content' => 'Docker is a platform for containerizing applications into isolated environments. Containers are lightweight, portable, and consistent across environments. Kubernetes (K8s) is an orchestration system for managing containers at scale. Key K8s concepts: Pods, Deployments, Services, Ingress, ConfigMaps, and Secrets. Use Docker Compose for local development and K8s for production.',
                'tags'    => ['docker', 'kubernetes', 'devops'],
            ],
            [
                'title'   => 'Homemade Sourdough Bread Recipe',
                'content' => 'Ingredients: 500g bread flour, 375ml water, 100g active starter, 10g salt. Method: Mix flour and water, autolyse 30 min. Add starter and salt, stretch and fold every 30 min for 2 hours. Bulk ferment 4-6 hours at room temperature. Shape, refrigerate overnight. Bake in Dutch oven at 250°C: 20 min covered, 25 min uncovered. Cool completely before slicing.',
                'tags'    => ['recipe', 'baking', 'food'],
            ],
            [
                'title'   => 'Stoic Philosophy — Daily Principles',
                'content' => 'Stoicism teaches that virtue is the only true good. Focus only on what is within your control: your thoughts, actions, and responses. Accept external events with equanimity. Key Stoic practices: negative visualization (imagining loss to appreciate what you have), the view from above (seeing problems from a cosmic perspective), and journaling at day end. Read Marcus Aurelius, Epictetus, and Seneca.',
                'tags'    => ['philosophy', 'stoicism', 'mindset'],
            ],
            [
                'title'   => 'JavaScript Async/Await Best Practices',
                'content' => 'Always use async/await over raw Promises for readability. Wrap await calls in try/catch for error handling. Use Promise.all() to run multiple async operations concurrently instead of sequentially. Avoid mixing async/await with .then() chains. For sequential operations that depend on each other, chain awaits. For independent operations, use Promise.all to save time. Never use async inside forEach — use for...of instead.',
                'tags'    => ['javascript', 'programming', 'web'],
            ],
            [
                'title'   => 'Home Gym Setup on a Budget',
                'content' => 'Essential equipment for a budget home gym: adjustable dumbbells ($150), pull-up bar ($30), resistance bands ($20), yoga mat ($25), and a jump rope ($15). Total: under $250. Focus compound movements: squats, deadlifts, push-ups, pull-ups, rows. Use YouTube for free workout programs. Progressive overload is key — increase weight or reps each week. Recovery matters as much as training.',
                'tags'    => ['fitness', 'gym', 'health'],
            ],
            [
                'title'   => 'Laravel Performance Optimization Tips',
                'content' => 'Key Laravel optimizations: 1) Use eager loading (with()) to prevent N+1 query problems. 2) Cache expensive queries with Redis. 3) Use database indexes on frequently queried columns. 4) Run php artisan optimize in production. 5) Use queues for slow operations like email sending and AI API calls. 6) Enable opcode caching (OPcache). 7) Use chunking for large dataset processing. Profile queries with Laravel Debugbar.',
                'tags'    => ['laravel', 'php', 'performance'],
            ],
        ];

        foreach ($notes as $note) {
            Note::create($note);
        }

        $this->command->info('✅ Seeded 10 sample notes successfully.');
    }
}
