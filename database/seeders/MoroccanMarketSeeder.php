<?php

namespace Database\Seeders;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MoroccanMarketSeeder extends Seeder
{
    private const COUNTRY = 'Morocco';
    private const CURRENCY = 'MAD';

    private array $cities = [
        'Casablanca' => ['Maarif', 'Sidi Maarouf', 'Hay Hassani', 'Ain Sebaa', 'Bourgogne'],
        'Rabat' => ['Agdal', 'Hay Riad', 'Yacoub El Mansour', 'Akkari', 'Souissi'],
        'Marrakech' => ['Gueliz', 'Sidi Ghanem', 'Mhamid', 'Daoudiat', 'Targa'],
        'Tanger' => ['Iberia', 'Malabata', 'Boukhalef', 'Beni Makada', 'Moghogha'],
        'Agadir' => ['Founty', 'Dakhla', 'Hay Mohammadi', 'Talborjt', 'Tilila'],
        'Fes' => ['Zouagha', 'Saiss', 'Narjiss', 'Atlas', 'Bensouda'],
        'Meknes' => ['Hamria', 'Sidi Bouzekri', 'Marjane', 'Borj Moulay Omar', 'Mansour'],
        'Oujda' => ['Hay Al Qods', 'Lazaret', 'Andalous', 'Sidi Yahya', 'Ennakhil'],
        'Kenitra' => ['Maamora', 'Val Fleuri', 'Bir Rami', 'Ismailia', 'Saknia'],
    ];

    private array $firstNames = [
        'Yassine', 'Imane', 'Mehdi', 'Sara', 'Zakaria', 'Oumaima', 'Hicham', 'Nadia', 'Rachid', 'Salma',
        'Karim', 'Soukaina', 'Aymane', 'Khadija', 'Reda', 'Ibtissam', 'Anas', 'Hajar', 'Ayoub', 'Meryem',
        'Hamza', 'Nour', 'Adil', 'Loubna', 'Youssef', 'Ines', 'Sofiane', 'Chaimae', 'Mustapha', 'Asmae',
    ];

    private array $lastNames = [
        'El Amrani', 'Bennani', 'Alaoui', 'Sahli', 'Chraibi', 'Tazi', 'Idrissi', 'Lahlou', 'Berrada', 'Kettani',
        'Mansouri', 'Fassi', 'Boussaid', 'Belkadi', 'Sefrioui', 'Najmi', 'Rami', 'Ouhaddou', 'Mouline', 'El Fassi',
    ];

    private array $userAgents = [
        'Mozilla/5.0 (Linux; Android 14; SM-A546B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Mobile Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile Safari/604.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_3) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Safari/605.1.15',
    ];

    public function run(): void
    {
        $workspaceColumns = Schema::hasTable('workspaces') ? Schema::getColumnListing('workspaces') : [];
        $workspaceSettingColumns = Schema::hasTable('workspace_settings') ? Schema::getColumnListing('workspace_settings') : [];

        // Prefer fake login admin so dashboard data appears on the account used for testing.
        $user = User::where('email', 'admin.demo@landingbuilder.local')->first()
            ?? User::firstOrCreate(
                ['email' => 'maroc.demo@landingbuilder.local'],
                [
                    'name' => 'Morocco Demo Admin',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

        $workspace = Workspace::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Moroccan SaaS Demo'],
            $this->filterColumns(['currency' => self::CURRENCY], $workspaceColumns)
        );

        if (Schema::hasTable('workspace_settings')) {
            WorkspaceSetting::updateOrCreate(
                ['workspace_id' => $workspace->id],
                $this->filterColumns([
                    'whatsapp_enabled' => true,
                    'whatsapp_phone' => '+212600112233',
                    'whatsapp_template_landing' => 'Salam {{name}}, merci pour votre interet. Nous revenons vers vous rapidement.',
                    'checkout_style' => 'style_2',
                    'thankyou_style' => 'style_2',
                ], $workspaceSettingColumns)
            );
        }

        $this->purgeWorkspaceData($workspace->id);

        $landings = $this->seedLandings($workspace->id);
        $catalog = $this->seedCatalog($landings);

        $checkoutLeadCount = $this->seedOrdersAndCheckoutLeads($landings, $catalog['products']);
        $extraLeadCount = $this->seedExtraLeads($landings, $catalog['products'], $catalog['services']);
        $this->seedAnalytics($landings);

        $this->command?->info("Moroccan Market Seeder completed: {$checkoutLeadCount} checkout leads, {$extraLeadCount} extra leads, MAD-only dataset.");
    }

    private function purgeWorkspaceData(int $workspaceId): void
    {
        $landingIds = Landing::where('workspace_id', $workspaceId)->pluck('id')->all();

        if (empty($landingIds)) {
            return;
        }

        if (Schema::hasTable('analytics_events')) {
            DB::table('analytics_events')->whereIn('landing_id', $landingIds)->delete();
        }

        if (Schema::hasTable('analytics_sessions')) {
            $visitorIds = DB::table('analytics_sessions')
                ->whereIn('landing_id', $landingIds)
                ->pluck('visitor_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            DB::table('analytics_sessions')->whereIn('landing_id', $landingIds)->delete();

            if (!empty($visitorIds) && Schema::hasTable('analytics_visitors')) {
                DB::table('analytics_visitors')->whereIn('id', $visitorIds)->delete();
            }
        }

        if (Schema::hasTable('leads')) {
            DB::table('leads')->whereIn('landing_id', $landingIds)->delete();
        }

        if (Schema::hasTable('orders')) {
            DB::table('orders')->whereIn('landing_id', $landingIds)->delete();
        }

        if (Schema::hasTable('products')) {
            DB::table('products')->whereIn('landing_id', $landingIds)->delete();
        }

        if (Schema::hasTable('landing_pages')) {
            DB::table('landing_pages')->whereIn('landing_id', $landingIds)->delete();
        }

        if (Schema::hasTable('landing_settings')) {
            DB::table('landing_settings')->whereIn('landing_id', $landingIds)->delete();
        }

        Landing::whereIn('id', $landingIds)->delete();
    }

    private function seedLandings(int $workspaceId): array
    {
        $landingColumns = Schema::getColumnListing('landings');
        $slugSuffix = '-w' . $workspaceId;

        $ecomMain = Landing::create($this->filterColumns([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'name' => 'FitZone Maroc - Correcteur de Posture',
            'slug' => 'fitzone-maroc-correcteur-posture' . $slugSuffix,
            'status' => 'published',
            'is_main' => true,
            'published_at' => now()->subDays(22),
            'enable_cart' => true,
            'cart_position' => 'bottom-right',
            'cart_x_offset' => 20,
            'cart_y_offset' => 20,
            'content_type' => 'landing',
            'source' => 'manual',
            'is_template' => false,
            'category' => 'ecommerce',
            'visibility' => 'private',
        ], $landingColumns));

        $ecomSecondary = Landing::create($this->filterColumns([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'name' => 'FitZone Maroc - Recuperation Sportive',
            'slug' => 'fitzone-maroc-recuperation-sportive' . $slugSuffix,
            'status' => 'published',
            'is_main' => false,
            'published_at' => now()->subDays(17),
            'enable_cart' => true,
            'cart_position' => 'bottom-right',
            'cart_x_offset' => 20,
            'cart_y_offset' => 20,
            'content_type' => 'landing',
            'source' => 'manual',
            'is_template' => false,
            'category' => 'ecommerce',
            'visibility' => 'private',
        ], $landingColumns));

        $agency = Landing::create($this->filterColumns([
            'uuid' => (string) Str::uuid(),
            'workspace_id' => $workspaceId,
            'name' => 'ClickBoost Maroc - Leads Qualifies',
            'slug' => 'clickboost-maroc-leads-qualifies' . $slugSuffix,
            'status' => 'published',
            'is_main' => false,
            'published_at' => now()->subDays(19),
            'enable_cart' => false,
            'content_type' => 'landing',
            'source' => 'manual',
            'is_template' => false,
            'category' => 'service',
            'visibility' => 'private',
        ], $landingColumns));

        $ecomMainIndex = LandingPage::create([
            'landing_id' => $ecomMain->id,
            'type' => 'index',
            'name' => 'LP Correcteur de Posture',
            'slug' => 'fitzone-posture-index',
            'status' => 'published',
            'html' => '<section><h1>Correcteur de posture FitZone</h1><p>Soulagez votre mal de dos en quelques jours et retrouvez une posture naturelle.</p><a href="checkout" class="cta">Commander maintenant</a></section>',
            'css' => '.cta{display:inline-block;padding:12px 20px;background:#0f766e;color:#fff;border-radius:8px;text-decoration:none}',
            'js' => '',
        ]);

        $ecomMainCheckout = LandingPage::create([
            'landing_id' => $ecomMain->id,
            'type' => 'checkout',
            'name' => 'Checkout FitZone',
            'slug' => 'fitzone-posture-checkout',
            'status' => 'published',
            'html' => '<section><h2>Finalisez votre commande</h2><p>Livraison rapide partout au Maroc - paiement a la livraison disponible.</p></section>',
        ]);

        LandingPage::create([
            'landing_id' => $ecomMain->id,
            'type' => 'thankyou',
            'name' => 'Merci FitZone',
            'slug' => 'fitzone-posture-thankyou',
            'status' => 'published',
            'html' => '<section><h2>Merci pour votre commande</h2><p>Notre equipe vous contacte tres vite pour confirmation.</p></section>',
        ]);

        $ecomSecondaryIndex = LandingPage::create([
            'landing_id' => $ecomSecondary->id,
            'type' => 'index',
            'name' => 'LP Recuperation',
            'slug' => 'fitzone-recuperation-index',
            'status' => 'published',
            'html' => '<section><h1>Recuperation sportive intelligente</h1><p>Genouilleres et accessoires pour entrainements intenses.</p><a href="checkout" class="cta">Commander maintenant</a></section>',
            'css' => '.cta{display:inline-block;padding:12px 20px;background:#1d4ed8;color:#fff;border-radius:8px;text-decoration:none}',
            'js' => '',
        ]);

        $ecomSecondaryCheckout = LandingPage::create([
            'landing_id' => $ecomSecondary->id,
            'type' => 'checkout',
            'name' => 'Checkout Recuperation',
            'slug' => 'fitzone-recuperation-checkout',
            'status' => 'published',
            'html' => '<section><h2>Paiement securise</h2><p>Option COD disponible dans toutes les grandes villes marocaines.</p></section>',
        ]);

        LandingPage::create([
            'landing_id' => $ecomSecondary->id,
            'type' => 'thankyou',
            'name' => 'Merci Recuperation',
            'slug' => 'fitzone-recuperation-thankyou',
            'status' => 'published',
            'html' => '<section><h2>Commande bien recue</h2><p>Merci. Confirmation WhatsApp en quelques minutes.</p></section>',
        ]);

        $agencyIndex = LandingPage::create([
            'landing_id' => $agency->id,
            'type' => 'index',
            'name' => 'LP ClickBoost',
            'slug' => 'clickboost-leads-index',
            'status' => 'published',
            'html' => '<section><h1>Recevez +30 leads qualifies en 7 jours</h1><p>Funnels + Meta Ads + WhatsApp CRM pour hotels, riads, cliniques et ecommerce.</p><a href="#call" class="cta">Reserver un appel</a></section>',
            'css' => '.cta{display:inline-block;padding:12px 20px;background:#111827;color:#fff;border-radius:8px;text-decoration:none}',
            'js' => '',
        ]);

        LandingPage::create([
            'landing_id' => $agency->id,
            'type' => 'thankyou',
            'name' => 'Merci ClickBoost',
            'slug' => 'clickboost-thankyou',
            'status' => 'published',
            'html' => '<section><h2>Demande envoyee</h2><p>Un consultant ClickBoost vous appelle sous 24h.</p></section>',
        ]);

        return [
            'ecom_main' => ['landing' => $ecomMain, 'index_page_id' => $ecomMainIndex->id, 'checkout_page_id' => $ecomMainCheckout->id],
            'ecom_secondary' => ['landing' => $ecomSecondary, 'index_page_id' => $ecomSecondaryIndex->id, 'checkout_page_id' => $ecomSecondaryCheckout->id],
            'agency' => ['landing' => $agency, 'index_page_id' => $agencyIndex->id, 'checkout_page_id' => null],
        ];
    }

    private function seedCatalog(array $landings): array
    {
        // Structured ecommerce product array (Morocco-focused, MAD pricing).
        $products = [
            [
                'name' => 'Correcteur de posture FitZone Pro',
                'short_description' => 'Redresse le dos et reduit la fatigue lombaire pendant le travail.',
                'price' => 199,
                'compare_at_price' => 279,
                'image' => 'https://placehold.co/600x600?text=Correcteur+Posture',
                'sku' => 'FZM-POST-001',
                'category' => 'Posture',
                'landing_key' => 'ecom_main',
            ],
            [
                'name' => 'Ceinture abdominale Thermo Active',
                'short_description' => 'Soutien abdominal et maintien du dos pour les journees chargees.',
                'price' => 249,
                'compare_at_price' => 329,
                'image' => 'https://placehold.co/600x600?text=Ceinture+Abdominale',
                'sku' => 'FZM-ABD-002',
                'category' => 'Fitness',
                'landing_key' => 'ecom_main',
            ],
            [
                'name' => 'Genouillere Sport Compression',
                'short_description' => 'Stabilise le genou pendant la course et les entrainements intensifs.',
                'price' => 149,
                'compare_at_price' => 219,
                'image' => 'https://placehold.co/600x600?text=Genouillere+Sport',
                'sku' => 'FZM-KNEE-003',
                'category' => 'Recuperation',
                'landing_key' => 'ecom_secondary',
            ],
            [
                'name' => 'Coussin orthopedique lombaire',
                'short_description' => 'Ameliore le confort assis au bureau et en voiture.',
                'price' => 179,
                'compare_at_price' => 249,
                'image' => 'https://placehold.co/600x600?text=Coussin+Orthopedique',
                'sku' => 'FZM-CUSH-004',
                'category' => 'Confort',
                'landing_key' => 'ecom_main',
            ],
            [
                'name' => 'Bande resistance fitness (Pack 5)',
                'short_description' => 'Renforcement musculaire maison avec 5 niveaux de resistance.',
                'price' => 129,
                'compare_at_price' => 189,
                'image' => 'https://placehold.co/600x600?text=Bande+Resistance',
                'sku' => 'FZM-BAND-005',
                'category' => 'Fitness',
                'landing_key' => 'ecom_secondary',
            ],
            [
                'name' => 'Rouleau massage recuperation',
                'short_description' => 'Detend les muscles apres sport et limite les douleurs du lendemain.',
                'price' => 159,
                'compare_at_price' => 229,
                'image' => 'https://placehold.co/600x600?text=Rouleau+Massage',
                'sku' => 'FZM-ROLL-006',
                'category' => 'Recuperation',
                'landing_key' => 'ecom_secondary',
            ],
        ];

        // Structured agency service array (stored in products table for architecture compatibility).
        $services = [
            [
                'title' => 'Creation Landing Page conversion',
                'description' => 'Landing page orientee conversion avec tracking complet et A/B hooks.',
                'price' => 2500,
            ],
            [
                'title' => 'Gestion Facebook Ads',
                'description' => 'Pilotage campagnes Meta Ads avec ciblage local Maroc et creatives UGC.',
                'price' => 4500,
            ],
            [
                'title' => 'Tunnel de vente complet',
                'description' => 'Landing + checkout + CRM WhatsApp + automatisations de suivi.',
                'price' => 8500,
            ],
            [
                'title' => 'Generation de leads WhatsApp',
                'description' => 'Acquisition leads qualifies avec scripts de qualification WhatsApp.',
                'price' => 3500,
            ],
            [
                'title' => 'Optimisation conversion CRO',
                'description' => 'Audit complet, heatmap analytics et optimisation du taux de transformation.',
                'price' => 3000,
            ],
        ];

        $createdProducts = [];
        foreach ($products as $item) {
            $landing = $landings[$item['landing_key']]['landing'];
            $record = Product::create([
                'landing_id' => $landing->id,
                'name' => $item['name'],
                'price' => $item['price'],
                'currency' => self::CURRENCY,
                'description' => $item['short_description']
                    . ' | SKU: ' . $item['sku']
                    . ' | Categorie: ' . $item['category']
                    . ' | Compare at: ' . $item['compare_at_price'] . ' MAD'
                    . ' | Image: ' . $item['image'],
                'label' => $item['category'],
                'is_bump' => false,
                'is_active' => true,
            ]);

            $createdProducts[] = array_merge($item, ['id' => $record->id, 'landing_id' => $landing->id]);
        }

        $createdServices = [];
        foreach ($services as $index => $item) {
            $record = Product::create([
                'landing_id' => $landings['agency']['landing']->id,
                'name' => $item['title'],
                'price' => $item['price'],
                'currency' => self::CURRENCY,
                'description' => $item['description'] . ' | SKU: CBM-SRV-00' . ($index + 1) . ' | Categorie: Service',
                'label' => 'Service',
                'is_bump' => false,
                'is_active' => true,
            ]);

            $createdServices[] = array_merge($item, ['id' => $record->id, 'landing_id' => $landings['agency']['landing']->id]);
        }

        return [
            'products' => $createdProducts,
            'services' => $createdServices,
        ];
    }

    private function seedOrdersAndCheckoutLeads(array $landings, array $products): int
    {
        $ordersCount = 32;
        $ordersTableExists = Schema::hasTable('orders');
        $orderColumns = $ordersTableExists ? Schema::getColumnListing('orders') : [];
        $leadColumns = Schema::getColumnListing('leads');
        $createdLeads = 0;

        $states = [
            ['human' => 'pending', 'order' => 'pending', 'lead' => 'pending', 'weight' => 22],
            ['human' => 'confirmed', 'order' => 'paid', 'lead' => 'paid', 'weight' => 20],
            ['human' => 'shipped', 'order' => 'shipped', 'lead' => 'shipped', 'weight' => 26],
            ['human' => 'delivered', 'order' => 'completed', 'lead' => 'completed', 'weight' => 24],
            ['human' => 'cancelled', 'order' => 'failed', 'lead' => 'failed', 'weight' => 8],
        ];

        for ($i = 0; $i < $ordersCount; $i++) {
            $customer = $this->randomCustomer();
            $state = $this->weightedPick($states);
            $paymentProvider = random_int(1, 100) <= 78 ? 'cod' : 'card';
            $createdAt = $this->randomRecentDatetime(true);

            $itemsCount = random_int(1, 100) <= 72 ? 1 : 2;
            $selectedProducts = collect($products)->shuffle()->take($itemsCount)->values()->all();

            $orderItems = [];
            $total = 0;
            foreach ($selectedProducts as $product) {
                $qty = random_int(1, 2);
                $lineTotal = $product['price'] * $qty;
                $total += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'qty' => $qty,
                    'price' => $product['price'],
                    'subtotal' => $lineTotal,
                ];
            }

            $mainProduct = $selectedProducts[0];
            $transactionId = 'ma_' . Str::upper(Str::random(12));

            if ($ordersTableExists) {
                $orderPayload = [
                    'landing_id' => $mainProduct['landing_id'],
                    'product_id' => $mainProduct['id'],
                    'customer_name' => $customer['full_name'],
                    'customer_email' => $customer['email'],
                    'amount' => $total,
                    'currency' => self::CURRENCY,
                    'status' => $state['order'],
                    'payment_provider' => $paymentProvider,
                    'transaction_id' => $transactionId,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addMinutes(random_int(5, 120)),
                ];

                if (in_array('metadata', $orderColumns, true)) {
                    $orderPayload['metadata'] = json_encode([
                        'phone' => $customer['phone'],
                        'city' => $customer['city'],
                        'address' => $customer['address'],
                        'human_status' => $state['human'],
                    ], JSON_UNESCAPED_UNICODE);
                }

                if (in_array('order_items', $orderColumns, true)) {
                    $orderPayload['order_items'] = json_encode($orderItems, JSON_UNESCAPED_UNICODE);
                }

                DB::table('orders')->insert($this->filterColumns($orderPayload, $orderColumns));
            }

            $checkoutPageId = $mainProduct['landing_id'] === $landings['ecom_secondary']['landing']->id
                ? $landings['ecom_secondary']['checkout_page_id']
                : $landings['ecom_main']['checkout_page_id'];

            $leadPayload = [
                'landing_id' => $mainProduct['landing_id'],
                'landing_page_id' => $checkoutPageId,
                'type' => 'checkout',
                'email' => $customer['email'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'city' => $customer['city'],
                'zip' => $customer['zip'],
                'country' => self::COUNTRY,
                'data' => json_encode([
                    'source_channel' => 'Checkout',
                    'message' => 'Confirmation de commande demandee.',
                    'order_status' => $state['human'],
                    'payment_method' => strtoupper($paymentProvider),
                    'items' => $orderItems,
                ], JSON_UNESCAPED_UNICODE),
                'status' => $state['lead'],
                'payment_provider' => $paymentProvider,
                'amount' => $total,
                'currency' => self::CURRENCY,
                'transaction_id' => $transactionId,
                'product_id' => $mainProduct['id'],
                'utm_source' => random_int(1, 100) <= 60 ? 'facebook' : 'instagram',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'maroc_conversion_checkout',
                'referrer' => random_int(1, 100) <= 65 ? 'https://www.facebook.com/' : 'https://www.instagram.com/',
                'order_items' => json_encode($orderItems, JSON_UNESCAPED_UNICODE),
                'ip_address' => $this->randomIp(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addMinutes(random_int(2, 70)),
            ];

            DB::table('leads')->insert($this->filterColumns($leadPayload, $leadColumns));
            $createdLeads++;
        }

        return $createdLeads;
    }

    private function seedExtraLeads(array $landings, array $products, array $services): int
    {
        $leadColumns = Schema::getColumnListing('leads');
        $messagesServices = [
            'Je veux plus de reservations pour mon riad a Marrakech.',
            'Besoin d une campagne Meta Ads pour clinique esthetique a Casablanca.',
            'On cherche un tunnel complet pour notre boutique Shopify.',
            'Interesse par generation de leads WhatsApp pour hotel a Agadir.',
            'Pouvez-vous auditer notre landing actuelle et augmenter le taux de conversion ?',
        ];
        $messagesProducts = [
            'Le correcteur est disponible en stock pour Casablanca ?',
            'Je veux commander deux pieces avec livraison rapide.',
            'Est-ce que la genouillere tient pour footing quotidien ?',
            'Je prefere paiement a la livraison, c est possible ?',
            'Je cherche un produit pour soulager le mal de dos au bureau.',
        ];

        $invalidPhones = ['+21261234', '+2126ABCD123', '0600000000', '+21269999'];
        $insertedPayloads = [];

        for ($i = 0; $i < 24; $i++) {
            $channelRoll = random_int(1, 100);
            $channel = $channelRoll <= 52 ? 'Landing Page' : ($channelRoll <= 87 ? 'WhatsApp' : 'Checkout');
            $isServiceLead = random_int(1, 100) <= 65;
            $target = $isServiceLead ? $services[array_rand($services)] : $products[array_rand($products)];
            $customer = $this->randomCustomer();
            $createdAt = $this->randomRecentDatetime(false);
            $status = $isServiceLead ? $this->weightedPick([
                ['value' => 'new', 'weight' => 50],
                ['value' => 'contacted', 'weight' => 35],
                ['value' => 'qualified', 'weight' => 15],
            ])['value'] : $this->weightedPick([
                ['value' => 'new', 'weight' => 45],
                ['value' => 'pending', 'weight' => 35],
                ['value' => 'contacted', 'weight' => 20],
            ])['value'];

            if (in_array($i, [5, 13, 21], true)) {
                $customer['phone'] = $invalidPhones[array_rand($invalidPhones)];
            }

            $landingKey = $isServiceLead ? 'agency' : ($target['landing_id'] === $landings['ecom_secondary']['landing']->id ? 'ecom_secondary' : 'ecom_main');
            $leadType = $channel === 'Checkout' ? 'checkout' : 'form';
            $message = $isServiceLead ? $messagesServices[array_rand($messagesServices)] : $messagesProducts[array_rand($messagesProducts)];

            $payload = [
                'landing_id' => $target['landing_id'],
                'landing_page_id' => $landings[$landingKey]['index_page_id'],
                'type' => $leadType,
                'email' => $customer['email'],
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'city' => $customer['city'],
                'zip' => $customer['zip'],
                'country' => self::COUNTRY,
                'data' => json_encode([
                    'source_channel' => $channel,
                    'message' => $message,
                    'full_name' => $customer['full_name'],
                    'service_id' => $isServiceLead ? $target['id'] : null,
                    'product_id' => $isServiceLead ? null : $target['id'],
                ], JSON_UNESCAPED_UNICODE),
                'status' => $status,
                'payment_provider' => $leadType === 'checkout' ? (random_int(1, 100) <= 80 ? 'cod' : 'card') : null,
                'amount' => $leadType === 'checkout' ? $target['price'] : null,
                'currency' => self::CURRENCY,
                'transaction_id' => $leadType === 'checkout' ? 'ld_' . Str::upper(Str::random(10)) : null,
                'product_id' => $target['id'],
                'utm_source' => $channel === 'WhatsApp' ? 'whatsapp' : ($isServiceLead ? 'facebook' : 'instagram'),
                'utm_medium' => $channel === 'Landing Page' ? 'social' : 'dm',
                'utm_campaign' => $isServiceLead ? 'clickboost_lead_gen' : 'fitzone_lp_offer',
                'referrer' => $channel === 'WhatsApp' ? 'https://wa.me/' : 'https://www.facebook.com/',
                'ip_address' => $this->randomIp(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addMinutes(random_int(2, 45)),
            ];

            DB::table('leads')->insert($this->filterColumns($payload, $leadColumns));
            $insertedPayloads[] = $payload;
        }

        // Duplicate leads for realism.
        $duplicates = 0;
        foreach (array_slice($insertedPayloads, 0, 4) as $original) {
            $dup = $original;
            $dup['created_at'] = Carbon::parse($original['created_at'])->addDays(random_int(2, 8));
            $dup['updated_at'] = Carbon::parse($dup['created_at'])->addMinutes(random_int(5, 40));
            DB::table('leads')->insert($this->filterColumns($dup, $leadColumns));
            $duplicates++;
        }

        return 24 + $duplicates;
    }

    private function seedAnalytics(array $landings): void
    {
        if (!Schema::hasTable('analytics_visitors') || !Schema::hasTable('analytics_sessions') || !Schema::hasTable('analytics_events')) {
            return;
        }

        $sessionColumns = Schema::getColumnListing('analytics_sessions');
        $eventColumns = Schema::getColumnListing('analytics_events');

        $visitorSeedRows = [];
        for ($i = 0; $i < 900; $i++) {
            $firstSeen = now()->subDays(random_int(10, 90))->subHours(random_int(0, 20));
            $lastSeen = now()->subDays(random_int(0, 20))->subMinutes(random_int(1, 59));
            $visitorSeedRows[] = [
                'visitor_id' => (string) Str::uuid(),
                'ip_hash' => hash('sha256', $this->randomIp()),
                'user_agent' => $this->userAgents[array_rand($this->userAgents)],
                'first_seen_at' => $firstSeen,
                'last_seen_at' => $lastSeen,
                'created_at' => $firstSeen,
                'updated_at' => $lastSeen,
            ];
        }

        foreach (array_chunk($visitorSeedRows, 300) as $chunk) {
            DB::table('analytics_visitors')->insert($chunk);
        }

        $visitorPool = DB::table('analytics_visitors')
            ->orderByDesc('id')
            ->limit(900)
            ->get(['id', 'visitor_id'])
            ->all();

        $landingWeights = [
            ['landing_id' => $landings['ecom_main']['landing']->id, 'key' => 'ecom_main', 'weight' => 50],
            ['landing_id' => $landings['agency']['landing']->id, 'key' => 'agency', 'weight' => 35],
            ['landing_id' => $landings['ecom_secondary']['landing']->id, 'key' => 'ecom_secondary', 'weight' => 15],
        ];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $day = now()->subDays($daysAgo)->startOfDay();
            $growthIndex = 29 - $daysAgo;
            $visitorsCount = 120 + ($growthIndex * 9) + random_int(0, 70);

            if ($day->isWeekend()) {
                $visitorsCount += random_int(70, 140);
            }

            if (in_array($daysAgo, [3, 10, 17, 24], true)) {
                $visitorsCount += random_int(60, 120);
            }

            if ($daysAgo <= 6) {
                // Strong current-period push so dashboard change cards stay positive.
                $visitorsCount += random_int(70, 130);
            }

            $visitorsCount = max(50, min(500, $visitorsCount));

            $sessionBatch = [];
            $eventBatch = [];

            for ($i = 0; $i < $visitorsCount; $i++) {
                $landingPick = $this->weightedPick($landingWeights);
                $channelPick = $this->weightedPick([
                    ['channel' => 'facebook_ads', 'weight' => 52],
                    ['channel' => 'tiktok_ads', 'weight' => 20],
                    ['channel' => 'instagram', 'weight' => 10],
                    ['channel' => 'organic', 'weight' => 12],
                    ['channel' => 'direct', 'weight' => 6],
                ]);

                $source = $this->mapTrafficSource($channelPick['channel']);
                $deviceType = $this->weightedPick([
                    ['value' => 'mobile', 'weight' => 72],
                    ['value' => 'desktop', 'weight' => 24],
                    ['value' => 'tablet', 'weight' => 4],
                ])['value'];

                $city = $this->randomCity();
                $sessionId = (string) Str::uuid();
                $visitor = $visitorPool[array_rand($visitorPool)];
                $startedAt = $day->copy()->addMinutes(random_int(0, 1439))->addSeconds(random_int(0, 59));
                $duration = random_int(20, 420);
                $lastActivity = $startedAt->copy()->addSeconds(min($duration, random_int(15, 260)));

                $sessionPayload = [
                    'session_id' => $sessionId,
                    'visitor_id' => $visitor->id,
                    'landing_id' => $landingPick['landing_id'],
                    'started_at' => $startedAt,
                    'last_activity_at' => $lastActivity,
                    'ended_at' => $startedAt->copy()->addSeconds($duration),
                    'duration_seconds' => $duration,
                    'is_bounce' => random_int(1, 100) <= 41,
                    'source_type' => $source['source_type'],
                    'referrer' => $source['referrer'],
                    'utm_source' => $source['utm_source'],
                    'utm_medium' => $source['utm_medium'],
                    'utm_campaign' => $source['utm_campaign'],
                    'utm_content' => $source['utm_content'],
                    'utm_term' => $source['utm_term'],
                    'device_type' => $deviceType,
                    'browser' => random_int(1, 100) <= 74 ? 'Chrome' : (random_int(1, 100) <= 60 ? 'Safari' : 'Firefox'),
                    'os' => $deviceType === 'desktop' ? 'Windows' : (random_int(1, 100) <= 55 ? 'Android' : 'iOS'),
                    'country' => self::COUNTRY,
                    'city' => $city,
                    'created_at' => $startedAt,
                    'updated_at' => $lastActivity,
                ];

                $sessionBatch[] = $this->filterColumns($sessionPayload, $sessionColumns);

                $pagePath = random_int(1, 100) <= 24 ? '/checkout' : '/';
                $eventBase = [
                    'session_id' => $sessionId,
                    'session_id_fk' => null,
                    'visitor_id' => $visitor->id,
                    'landing_id' => $landingPick['landing_id'],
                    'event_name' => 'pageview',
                    'url_path' => $pagePath,
                    'event_data' => json_encode(['channel' => $channelPick['channel']], JSON_UNESCAPED_UNICODE),
                    'element_label' => null,
                    'element_type' => null,
                    'element_position' => null,
                    'created_at' => $startedAt,
                    'updated_at' => $startedAt,
                ];
                $eventBatch[] = $this->filterColumns($eventBase, $eventColumns);

                if (random_int(1, 100) <= 18) {
                    $eventBatch[] = $this->filterColumns(array_merge($eventBase, [
                        'event_name' => 'cta_click',
                        'url_path' => '/',
                        'event_data' => json_encode(['label' => $landingPick['key'] === 'agency' ? 'Reserver un appel' : 'Commander maintenant'], JSON_UNESCAPED_UNICODE),
                        'element_label' => $landingPick['key'] === 'agency' ? 'Reserver un appel' : 'Commander maintenant',
                        'element_type' => 'button',
                        'element_position' => 'hero',
                        'created_at' => $startedAt->copy()->addSeconds(random_int(8, 55)),
                        'updated_at' => $startedAt->copy()->addSeconds(random_int(8, 55)),
                    ]), $eventColumns);
                }

                if (random_int(1, 100) <= 11) {
                    $eventBatch[] = $this->filterColumns(array_merge($eventBase, [
                        'event_name' => 'form_start',
                        'url_path' => $landingPick['key'] === 'agency' ? '/#lead-form' : '/checkout',
                        'event_data' => json_encode(['step' => 'contact'], JSON_UNESCAPED_UNICODE),
                        'created_at' => $startedAt->copy()->addSeconds(random_int(20, 120)),
                        'updated_at' => $startedAt->copy()->addSeconds(random_int(20, 120)),
                    ]), $eventColumns);
                }
            }

            foreach (array_chunk($sessionBatch, 400) as $chunk) {
                DB::table('analytics_sessions')->insert($chunk);
            }
            foreach (array_chunk($eventBatch, 800) as $chunk) {
                DB::table('analytics_events')->insert($chunk);
            }
        }

        // Keep a few active users to make realtime widgets non-empty.
        $activeSessions = [];
        $activeEvents = [];
        for ($i = 0; $i < 12; $i++) {
            $visitor = $visitorPool[array_rand($visitorPool)];
            $city = $this->randomCity();
            $landingPick = $landingWeights[array_rand($landingWeights)];
            $sessionId = (string) Str::uuid();
            $startedAt = now()->subMinutes(random_int(1, 5));
            $lastActivity = now()->subSeconds(random_int(20, 180));

            $activeSessions[] = $this->filterColumns([
                'session_id' => $sessionId,
                'visitor_id' => $visitor->id,
                'landing_id' => $landingPick['landing_id'],
                'started_at' => $startedAt,
                'last_activity_at' => $lastActivity,
                'ended_at' => null,
                'duration_seconds' => random_int(30, 380),
                'is_bounce' => false,
                'source_type' => 'social',
                'referrer' => 'https://www.facebook.com/',
                'utm_source' => 'facebook',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'retarget_maroc',
                'device_type' => 'mobile',
                'browser' => 'Chrome',
                'os' => 'Android',
                'country' => self::COUNTRY,
                'city' => $city,
                'created_at' => $startedAt,
                'updated_at' => $lastActivity,
            ], $sessionColumns);

            $activeEvents[] = $this->filterColumns([
                'session_id' => $sessionId,
                'session_id_fk' => null,
                'visitor_id' => $visitor->id,
                'landing_id' => $landingPick['landing_id'],
                'event_name' => 'pageview',
                'url_path' => '/',
                'event_data' => json_encode(['realtime' => true], JSON_UNESCAPED_UNICODE),
                'created_at' => $lastActivity,
                'updated_at' => $lastActivity,
            ], $eventColumns);
        }

        DB::table('analytics_sessions')->insert($activeSessions);
        DB::table('analytics_events')->insert($activeEvents);
    }

    private function randomCustomer(): array
    {
        $first = $this->firstNames[array_rand($this->firstNames)];
        $last = $this->lastNames[array_rand($this->lastNames)];
        $city = $this->randomCity();
        $neighborhood = $this->cities[$city][array_rand($this->cities[$city])];
        $streetNo = random_int(8, 188);

        return [
            'first_name' => $first,
            'last_name' => $last,
            'full_name' => "{$first} {$last}",
            'phone' => '+2126' . random_int(10000000, 99999999),
            'email' => Str::lower(Str::ascii($first . '.' . str_replace(' ', '', $last))) . random_int(10, 99) . '@gmail.com',
            'city' => $city,
            'address' => "{$streetNo}, {$neighborhood}",
            'zip' => (string) random_int(10000, 99999),
        ];
    }

    private function randomCity(): string
    {
        $cities = array_keys($this->cities);
        return $cities[array_rand($cities)];
    }

    private function randomRecentDatetime(bool $preferWeekend): Carbon
    {
        $targetDay = null;

        if (random_int(1, 100) <= 68) {
            // Bias most lead/order timestamps to current 7-day window.
            $targetDay = now()->subDays(random_int(0, 6))->startOfDay();
        }

        if (!$targetDay && $preferWeekend && random_int(1, 100) <= 45) {
            $weekendDays = [];
            for ($i = 0; $i < 30; $i++) {
                $day = now()->subDays($i)->startOfDay();
                if ($day->isWeekend()) {
                    $weekendDays[] = $day;
                }
            }
            $targetDay = $weekendDays[array_rand($weekendDays)];
        }

        if (!$targetDay) {
            $targetDay = now()->subDays(random_int(0, 29))->startOfDay();
        }

        return $targetDay->copy()->addHours(random_int(8, 22))->addMinutes(random_int(0, 59))->addSeconds(random_int(0, 59));
    }

    private function mapTrafficSource(string $channel): array
    {
        return match ($channel) {
            'facebook_ads' => [
                'source_type' => 'paid',
                'referrer' => 'https://www.facebook.com/',
                'utm_source' => 'facebook',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'morocco_scaling_campaign',
                'utm_content' => 'video_ugc_1',
                'utm_term' => 'posture',
            ],
            'instagram' => [
                'source_type' => 'social',
                'referrer' => 'https://www.instagram.com/',
                'utm_source' => 'instagram',
                'utm_medium' => 'social',
                'utm_campaign' => 'reels_engagement_ma',
                'utm_content' => 'reel_before_after',
                'utm_term' => 'fitness',
            ],
            'tiktok_ads' => [
                'source_type' => 'paid',
                'referrer' => 'https://www.tiktok.com/',
                'utm_source' => 'tiktok',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'tiktok_spark_ads_ma',
                'utm_content' => 'creator_clip',
                'utm_term' => 'mal_dos',
            ],
            'organic' => [
                'source_type' => 'search',
                'referrer' => 'https://www.google.com/',
                'utm_source' => 'google',
                'utm_medium' => 'organic',
                'utm_campaign' => 'seo_maroc',
                'utm_content' => null,
                'utm_term' => 'correcteur posture maroc',
            ],
            default => [
                'source_type' => 'direct',
                'referrer' => null,
                'utm_source' => null,
                'utm_medium' => null,
                'utm_campaign' => null,
                'utm_content' => null,
                'utm_term' => null,
            ],
        };
    }

    private function randomIp(): string
    {
        return random_int(2, 223) . '.' . random_int(0, 255) . '.' . random_int(0, 255) . '.' . random_int(1, 254);
    }

    private function weightedPick(array $options): array
    {
        $sum = array_sum(array_column($options, 'weight'));
        $roll = random_int(1, max($sum, 1));
        $running = 0;

        foreach ($options as $option) {
            $running += $option['weight'];
            if ($roll <= $running) {
                return $option;
            }
        }

        return $options[array_key_last($options)];
    }

    private function filterColumns(array $payload, array $columns): array
    {
        return array_intersect_key($payload, array_flip($columns));
    }
}
