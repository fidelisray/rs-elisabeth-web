<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @include('components.seo-meta', ['title' => __('Jadwal Dokter') . ' - RS St. Elisabeth Semarang'])
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    /* =====================
       Jadwal Dokter Styles
       ===================== */

    .jadwal-wrapper {
      padding: 1.5rem 0;
    }

    .jadwal-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1.25rem;
      color: #212529;
    }

    /* Scroll horizontal di mobile */
    .jadwal-scroll {
      overflow-x: auto;
    }

    /* Grid 7 kolom: 1 per hari */
    .jadwal-grid {
      display: grid;
      grid-template-columns: repeat(7, minmax(120px, 1fr));
      gap: 8px;
      min-width: 860px;
    }

    /* ---- Header hari ---- */
    .day-header {
      text-align: center;
      padding: 10px 6px;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      background-color: #f8f9fa;
      color: #6c757d;
      border: 1px solid #dee2e6;
    }

    .day-header.today {
      background-color: #4caf50;
      color: #fff;
      border-color: #4caf50;
    }

    /* ---- Kolom slot ---- */
    .slot-col {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    /* ---- Card jadwal ---- */
    .schedule-card {
      border-radius: 8px;
      border: 1px solid #dee2e6;
      background-color: #fff;
      padding: 10px 10px;
      font-size: 0.78rem;
      min-height: 78px;
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .schedule-card .sc-time {
      font-weight: 600;
      color: #212529;
      font-size: 0.8rem;
    }

    .schedule-card .sc-room {
      font-size: 0.75rem;
      color: #6c757d;
    }

    .schedule-card .sc-klinik {
      font-size: 0.75rem;
      color: #6c757d;
    }

    /* Card hari ini — hijau */
    .schedule-card.today-card {
      background-color: #4caf50;
      border-color: #4caf50;
    }

    .schedule-card.today-card .sc-time {
      color: #fff;
    }

    .schedule-card.today-card .sc-room,
    .schedule-card.today-card .sc-klinik {
      color: rgba(255, 255, 255, 0.85);
    }

    /* Slot kosong — tidak terlihat tapi tetap menjaga tinggi grid */
    .schedule-card.empty-card {
      background-color: transparent;
      border-color: transparent;
      pointer-events: none;
    }
  </style>
</head>
<body>

<div class="container py-4">
  <div class="jadwal-wrapper">
    <h2 class="jadwal-title">{{ __('Jadwal Dokter') }}</h2>

    <div class="jadwal-scroll">
      <div class="jadwal-grid" id="jadwalGrid">

        {{-- ============================================================
             HEADER HARI
             Tambahkan class "today" pada hari yang aktif.
             Untuk dynamic: bandingkan $hari dengan hari saat ini di Blade.
             ============================================================ --}}

        <div class="day-header today">{{ __('Senin') }}</div>
        <div class="day-header">{{ __('Selasa') }}</div>
        <div class="day-header">{{ __('Rabu') }}</div>
        <div class="day-header">{{ __('Kamis') }}</div>
        <div class="day-header">{{ __('Jumat') }}</div>
        <div class="day-header">{{ __('Sabtu') }}</div>
        <div class="day-header">{{ __('Minggu') }}</div>

        {{-- ============================================================
             KOLOM SENIN (hari ini — semua card pakai .today-card)
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card today-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card today-card">
            <span class="sc-time">09:15 - 12:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
          <div class="schedule-card today-card">
            <span class="sc-time">13:00 - 14:00</span>
            <span class="sc-room">ONL1</span>
            <span class="sc-klinik">Klinik Online</span>
          </div>
          <div class="schedule-card today-card">
            <span class="sc-time">18:00 - 19:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
        </div>

        {{-- ============================================================
             KOLOM SELASA
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">09:15 - 12:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">13:00 - 14:00</span>
            <span class="sc-room">ONL1</span>
            <span class="sc-klinik">Klinik Online</span>
          </div>
          {{-- Slot kosong: gunakan empty-card agar grid tetap rapi --}}
          <div class="schedule-card empty-card"></div>
        </div>

        {{-- ============================================================
             KOLOM RABU
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">09:15 - 12:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">13:00 - 14:00</span>
            <span class="sc-room">ONL1</span>
            <span class="sc-klinik">Klinik Online</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">18:00 - 19:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
        </div>

        {{-- ============================================================
             KOLOM KAMIS
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">09:15 - 12:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">13:00 - 14:00</span>
            <span class="sc-room">ONL1</span>
            <span class="sc-klinik">Klinik Online</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">18:00 - 19:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
        </div>

        {{-- ============================================================
             KOLOM JUM'AT
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">09:15 - 12:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">13:00 - 14:00</span>
            <span class="sc-room">ONL3</span>
            <span class="sc-klinik">Klinik Online</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">18:00 - 19:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
        </div>

        {{-- ============================================================
             KOLOM SABTU
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card">
            <span class="sc-time">09:15 - 12:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
          <div class="schedule-card empty-card"></div>
          <div class="schedule-card">
            <span class="sc-time">18:00 - 19:30</span>
            <span class="sc-room">C103</span>
            <span class="sc-klinik">Klinik Eksekutif Catleya</span>
          </div>
        </div>

        {{-- ============================================================
             KOLOM MINGGU
             ============================================================ --}}
        <div class="slot-col">
          <div class="schedule-card">
            <span class="sc-time">08:00 - 09:00</span>
            <span class="sc-room">D112</span>
            <span class="sc-klinik">Klinik Regular Dahlia</span>
          </div>
          <div class="schedule-card empty-card"></div>
          <div class="schedule-card empty-card"></div>
          <div class="schedule-card empty-card"></div>
        </div>

      </div>{{-- end jadwal-grid --}}
    </div>{{-- end jadwal-scroll --}}
  </div>{{-- end jadwal-wrapper --}}
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @include('components.json-ld')
    </body>
</html>
