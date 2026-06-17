<?php

namespace Database\Seeders;

use App\Models\ImageSignalement;
use App\Models\Signalement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Administrateur ────────────────────────────────────────────────────
        $admin = User::create([
            'nom'      => 'Administrateur',
            'email'    => 'admin@citoyen.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ── Gestionnaire ──────────────────────────────────────────────────────
        $gestionnaire = User::create([
            'nom'      => 'Jean Gestionnaire',
            'email'    => 'gestionnaire@citoyen.test',
            'password' => Hash::make('password'),
            'role'     => 'gestionnaire',
        ]);

        // ── Citoyens de test ──────────────────────────────────────────────────
        $citoyen1 = User::create([
            'nom'      => 'Kofi Ameko',
            'email'    => 'kofi@citoyen.test',
            'password' => Hash::make('password'),
            'role'     => 'citoyen',
        ]);

        $citoyen2 = User::create([
            'nom'      => 'Ama Sossou',
            'email'    => 'ama@citoyen.test',
            'password' => Hash::make('password'),
            'role'     => 'citoyen',
        ]);

        // ── Signalements de démo ──────────────────────────────────────────────
        $signalementsData = [
            [
                'utilisateur_id' => $citoyen1->id,
                'type'           => 'inondation',
                'description'    => 'Une inondation importante bloque la route principale du quartier Bè depuis ce matin. L\'eau monte progressivement.',
                'latitude'       => 6.1375,
                'longitude'      => 1.2123,
                'statut'         => 'en_cours',
            ],
            [
                'utilisateur_id' => $citoyen1->id,
                'type'           => 'chaussee',
                'description'    => 'Un nid de poule profond d\'environ 40 cm de diamètre sur le boulevard du 13 janvier. Dangereux pour les motos.',
                'latitude'       => 6.1410,
                'longitude'      => 1.2158,
                'statut'         => 'accepte',
            ],
            [
                'utilisateur_id' => $citoyen2->id,
                'type'           => 'electricite',
                'description'    => 'Un poteau électrique est tombé et les fils sont à terre dans la rue des Cocotiers. Risque d\'électrocution.',
                'latitude'       => 6.1320,
                'longitude'      => 1.2200,
                'statut'         => 'traite',
            ],
            [
                'utilisateur_id' => $citoyen2->id,
                'type'           => 'dechets',
                'description'    => 'Accumulation massive d\'ordures au carrefour du marché. Les bennes ne sont pas passées depuis 3 semaines.',
                'latitude'       => 6.1450,
                'longitude'      => 1.2080,
                'statut'         => 'en_cours',
            ],
            [
                'utilisateur_id' => $citoyen1->id,
                'type'           => 'eau',
                'description'    => 'Fuite importante de canalisation d\'eau au niveau de la rue de la Paix. Eau qui gicle depuis 2 jours.',
                'latitude'       => 6.1290,
                'longitude'      => 1.2140,
                'statut'         => 'rejete',
            ],
        ];

        foreach ($signalementsData as $data) {
            Signalement::create($data);
        }

        $this->command->info('✅ Seeder terminé.');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Admin',        'admin@citoyen.test',        'password'],
                ['Gestionnaire', 'gestionnaire@citoyen.test', 'password'],
                ['Citoyen 1',    'kofi@citoyen.test',         'password'],
                ['Citoyen 2',    'ama@citoyen.test',          'password'],
            ]
        );
    }
}
