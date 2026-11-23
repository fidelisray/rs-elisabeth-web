<?php

namespace Tests\Feature;

use Tests\TestCase;

class TentangKamiPageTest extends TestCase
{
    public function test_tentang_kami_renders_home_style_section_titles_for_motto_visi_and_misi_in_indonesian(): void
    {
        $response = $this->get('/tentang-kami');
        $response->assertOk();

        $html = $response->getContent();

        $this->assertSame(3, substr_count($html, 'class="section-title text-center'));
        $this->assertSame(3, substr_count($html, '<div class="divider"></div>'));
        $this->assertStringContainsString('<h2 class="fw-bold">Motto</h2>', $html);
        $this->assertStringContainsString('<h2 class="fw-bold">Visi</h2>', $html);
        $this->assertStringContainsString('<h2 class="fw-bold">Misi</h2>', $html);
        $this->assertStringContainsString('<blockquote class="motto-quote display-6 mb-0">"Pancaran Cintanya Menyembuhkan Derita Sesama"</blockquote>', $html);
        $this->assertStringContainsString('Sejarah Singkat', $html);
        $this->assertStringNotContainsString('badge bg-primary px-3 py-2 rounded-pill', $html);
    }

    public function test_tentang_kami_translates_section_titles_to_english(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/tentang-kami');
        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('<h2 class="fw-bold">Motto</h2>', $html);
        $this->assertStringContainsString('<h2 class="fw-bold">Vision</h2>', $html);
        $this->assertStringContainsString('<h2 class="fw-bold">Mission</h2>', $html);
    }
}
