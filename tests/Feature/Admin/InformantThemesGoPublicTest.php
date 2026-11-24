<?php

namespace Tests\Feature\Admin;

use App\Support\Expert;

class InformantThemesGoPublicTest extends AdminTestCase
{
    public function test_themes_saved_by_the_informant_appear_on_the_public_page_right_away(): void
    {
        config(['patung.expert_token' => str_repeat('t', 32)]);
        $statue = $this->statue(['name' => 'Patung Baru dari Admin', 'slug' => 'patung-baru-dari-admin']);

        $this->get('/id/patung/patung-baru-dari-admin')->assertSee('Menunggu klasifikasi informan');

        $this->withSession([Expert::SESSION_KEY => Expert::fingerprint(str_repeat('t', 32))])
            ->putJson('/id/validasi/'.$statue->slug, ['themes' => ['religi', 'itihasa']])
            ->assertOk();

        $this->assertSame(['itihasa', 'religi'], $statue->fresh()->themes()->orderBy('sort_order')->pluck('slug')->all());
        $this->get('/id/patung/patung-baru-dari-admin')->assertDontSee('Menunggu klasifikasi informan');
    }

    public function test_notes_only_saves_leave_public_themes_alone(): void
    {
        config(['patung.expert_token' => str_repeat('t', 32)]);
        $statue = $this->statue();
        $statue->themes()->attach(\App\Models\Theme::query()->where('slug', 'itihasa')->value('id'));

        $this->withSession([Expert::SESSION_KEY => Expert::fingerprint(str_repeat('t', 32))])
            ->putJson('/id/validasi/'.$statue->slug, ['notes' => 'Catatan saja'])
            ->assertOk();

        $this->assertSame(['itihasa'], $statue->fresh()->themes->pluck('slug')->all());
    }
}
