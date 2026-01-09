{{-- resources/views/pdf/cmr-template.blade.php --}}
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>CMR</title>

   <style>
    /* =========================================================
       ✅ CMR STYLE (DomPDF-safe)
       - 1 page
       - guaranteed margins via padding (not @page)
       - fixed A4 width in mm to prevent right overflow
       - all widths in mm or safe %, with box-sizing
    ========================================================= */

    @font-face {
        font-family: 'DejaVu Sans';
        src: url('{{ public_path('fonts/DejaVuSans.ttf') }}') format('truetype');
    }

    /* DomPDF: лучше держать margin=0 и делать поля через padding */
    @page { size: A4; margin: 0; }

    * { box-sizing: border-box; }

    html, body {
        font-family: 'DejaVu Sans', sans-serif;
        margin: 0;
        padding: 0;
        color: #000;
        font-size: 9.0px;
        line-height: 1.03;
    }

    /* =========================================================
       ✅ PAGE GEOMETRY
       A4 = 210mm x 297mm
       padding = 12mm => inner width = 186mm
    ========================================================= */
    .sheet{
        width: 210mm;            /* фикс под A4 */
    
        padding: 12mm;           /* реальные поля */
        margin: 0 auto;
        position: relative;
        overflow: hidden;        /* чтоб ничего не вылезало наружу */
    }

    .outer{
        width: 186mm;            /* 210 - 12 - 12 */
        border: 1.2px solid #000;
        position: relative;
        z-index: 2;
        background: transparent;
    }

    /* =========================================================
       WATERMARK
    ========================================================= */
  .wm-oval{
    position: absolute;
    left: 50%;
    top: 56%;
    transform: translate(-50%, -50%);
    width: 155mm;
    height: 92mm;
    border: 2.2mm solid rgba(0,0,0,0.12);
    border-radius: 9999px;
    z-index: 0;
    pointer-events: none;
}
.wm-text{
    position: absolute;
    left: 50%;
    top: 56%;
    transform: translate(-50%, -50%);
    font-size: 96px;
    font-weight: 700;
    letter-spacing: 2px;
    color: rgba(0,0,0,0.10);
    z-index: 0;
    pointer-events: none;
}

    /* =========================================================
       TABLE BASICS
    ========================================================= */
    table{ width: 100%; border-collapse: collapse; table-layout: fixed; }

    td, th{
        border: 0.75px solid #000;
        vertical-align: top;
        padding: 1.0mm 1.3mm;        /* компактнее, чтобы влезало по высоте */
        word-break: break-word;
        overflow: hidden;
    }

    .no-pad{ padding: 0 !important; }
    .center{ text-align: center; }
    .right { text-align: right;  }

    /* =========================================================
       TEXT HELPERS
    ========================================================= */
    .num{
        font-weight: 700;
        font-size: 12px;
        display: inline-block;
        width: 7mm;
    }

    .lbl{ font-size: 8px; }
    .sub{ display:block; font-size: 7px; margin-top: 0.5mm; }

    .cmrnr{
        width: 186mm;              /* чтобы совпадало с outer */
        font-weight: 700;
        font-size: 12px;
        text-align: right;
        margin: 0 0 0.8mm 0;
        position: relative;
        z-index: 2;
    }

    .title{
        font-weight: 700;
        font-size: 14px;
        text-align: center;
        letter-spacing: .2px;
    }
    .tiny{ font-size: 7.2px; margin-top: 2.0mm; }

    .pad-s{ padding: 0.8mm 1.0mm !important; }
    .mini { font-size: 7px; }

    /* =========================================================
       LINES INSIDE FIELDS
    ========================================================= */
    .lines{ margin-top: 1.0mm; }
    .line{
        border-bottom: 0.6px solid #000;
        height: 3.2mm;
    }
    .line:last-child{ border-bottom: none; }

    /* =========================================================
       ✅ FIXED HEIGHTS (1 PAGE)
       Подогнано так, чтобы 21–26 не уходили на вторую страницу
    ========================================================= */
    .h1  { height: 18mm; }
    .h2  { height: 18mm; }
    .h3  { height: 12mm; }
    .h4  { height: 12mm; }
    .h5  { height: 8mm;  }

    .htitle { height: 24mm; }
    .h16 { height: 16mm; }
    .h17 { height: 16mm; }
    .h18 { height: 20mm; }

    /* 6–11 уменьшено */
    .h611_head { height: 9mm; }
    .h611_body { height: 28mm; }
    .h611_adr  { height: 8mm; }

    .h13_19 { height: 34mm; }
    .h14    { height: 8mm;  }
    .h15_20 { height: 8mm;  }
    .h21_24 { height: 22mm; }
    .h25_26 { height: 12mm; }

    /* =========================================================
       OPTIONAL: если вдруг всё ещё чуть "жмёт" вниз,
       можешь уменьшить только эти 2 значения:
       .h611_body -> 26mm
       .h13_19    -> 32mm
    ========================================================= */
</style>


</head>
<body>
@php $cmr_nr = $cmr_nr ?? ''; @endphp



<div class="sheet">
    <div class="wm-oval"></div>
<div class="wm-text">CMR</div>
    <div class="cmrnr">CMR Nr. {{ $cmr_nr }}</div>

    <table class="outer">
        {{-- TOP --}}
        <tr>
            {{-- LEFT 1–5 (≈110/190 = 57.8947%) --}}
            <td class="no-pad" style="width:57.8947%;">
                <table>
                    <tr>
                        <td class="h1">
                            <div><span class="num">1</span><span class="lbl">Nosūtītājs (nosaukums, adrese, valsts)
                                <span class="sub">Absender (Name, Anschrift, Land)</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h2">
                            <div><span class="num">2</span><span class="lbl">Saņēmējs (nosaukums, adrese, valsts)
                                <span class="sub">Empfänger (Name, Anschrift, Land)</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h3">
                            <div><span class="num">3</span><span class="lbl">Kravas izkraušanas vieta
                                <span class="sub">Auslieferungsort des Gutes</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h4">
                            <div><span class="num">4</span><span class="lbl">Kravas iekraušanas vieta un datums
                                <span class="sub">Ort und Tag der Übernahme des Gutes</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h5">
                            <div><span class="num">5</span><span class="lbl">Pievienotie dokumenti
                                <span class="sub">Beigefügte dokumente</span></span></div>
                            <div class="lines"><div class="line"></div></div>
                        </td>
                    </tr>
                </table>
            </td>

            {{-- RIGHT Title + 16–18 (≈80/190 = 42.1053%) --}}
            <td class="no-pad" style="width:42.1053%;">
                <table>
                    <tr>
                        <td class="htitle">
                            <div class="title">
                                STARPTAUTISKĀ PREČU-TRANSPORTA PAVADZĪME<br>
                                INTERNATIONAL CONSIGNMENT NOTE
                            </div>
                            <div class="tiny">Šis pārvadājums ir veicams saskaņā ar CMR konvenciju.</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h16">
                            <div><span class="num">16</span><span class="lbl">Pārvadātājs/ekspeditors (nosaukums, adrese, valsts)
                                <span class="sub">Frachtführer (Name, Anschrift, Land)</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h17">
                            <div><span class="num">17</span><span class="lbl">Turpmākais pārvadātājs (nosaukums, adrese, valsts)
                                <span class="sub">Nachfolgende Frachtführer (Name, Anschrift, Land)</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="h18">
                            <div><span class="num">18</span><span class="lbl">Pārvadātāja aizrādījumi un piezīmes
                                <span class="sub">Vorbehalte und Bemerkungen des Frachtführer</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- 6–11 --}}
        <tr>
            <td colspan="2" class="no-pad">
                <table>
                    <tr class="center">
                        <td class="h611_head pad-s" style="width:14.7368%;"><span class="num">6</span><div class="lbl">Zīmes un numuri<span class="sub">Kennzeichen und Nummer</span></div></td>
                        <td class="h611_head pad-s" style="width:9.4737%;"><span class="num">7</span><div class="lbl">Vietu skaits<span class="sub">Anzahl der Packstücke</span></div></td>
                        <td class="h611_head pad-s" style="width:12.6316%;"><span class="num">8</span><div class="lbl">Iepakojuma veids<span class="sub">Art der Verpackung</span></div></td>
                        <td class="h611_head pad-s" style="width:38.9474%;"><span class="num">9</span><div class="lbl">Kravas nosaukums<span class="sub">Bezeichnung des Gutes</span></div></td>
                        <td class="h611_head pad-s" style="width:12.1053%;"><span class="num">10</span><div class="lbl">Statist Nr.<span class="sub">&nbsp;</span></div></td>
                        <td class="h611_head pad-s" style="width:12.1053%;"><span class="num">11</span><div class="lbl">Bruto svars, kg.<span class="sub">&nbsp;</span></div></td>
                    </tr>

                    <tr>
                        <td colspan="6" class="h611_body no-pad">
                            <div style="padding: 1.0mm 1.6mm;">
                                @for($i=0;$i<10;$i++) <div class="line"></div> @endfor
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="4" class="h611_adr pad-s mini">
                            Klase / Klasse &nbsp;&nbsp; Cipars / Ziffer &nbsp;&nbsp; Burts / Buchstabe &nbsp;&nbsp; ADR (ADR)
                        </td>
                        <td colspan="2" class="h611_adr pad-s"></td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- 13 + 19 --}}
        <tr>
            <td class="no-pad" style="width:75.7895%;"> {{-- 144/190 --}}
                <table>
                    <tr>
                        <td class="h13_19">
                            <div><span class="num">13</span><span class="lbl">Nosūtītāja norādījumi (muitas u.c. formalitātes)
                                <span class="sub">Absenders (Zoll und sonstige amtliche Behandlung)</span></span></div>
                            <div class="lines"><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div><div class="line"></div></div>
                            <div class="mini" style="margin-top:2mm;">
                                Norādīta kravas vērtība
                                <span style="float:right;">Angabe des Wertes des Gutes</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>

            <td class="no-pad" style="width:24.2105%;"> {{-- 46/190 --}}
                <table>
                    <tr>
                        <td class="h13_19 no-pad">
                            <table>
                                <tr>
                                    <td style="width:22%;" class="pad-s"><span class="num">19</span></td>
                                    <td style="width:39%;" class="pad-s lbl center">Sūtītājs</td>
                                    <td style="width:39%;" class="pad-s lbl center">Valūta</td>
                                </tr>
                                <tr><td colspan="3" class="pad-s mini">Likme / Fracht</td></tr>
                                <tr><td colspan="3" class="pad-s mini">Starppība / Zwischensumme</td></tr>
                                <tr><td colspan="3" class="pad-s mini">Papildu iekasējumi</td></tr>
                                <tr><td colspan="3" class="pad-s mini">Citi / Sonstiges</td></tr>
                                <tr><td colspan="3" class="pad-s mini">Kopā</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- 14 --}}
        <tr>
            <td colspan="2" class="h14"><span class="num">14</span></td>
        </tr>

        {{-- 15 + 20 --}}
        <tr>
            <td class="h15_20"><span class="num">15</span> <span class="lbl">Apmaksas noteikumi</span></td>
            <td class="h15_20"><span class="num">20</span> <span class="lbl">Īpaši saskaņoti noteikumi</span></td>
        </tr>

        {{-- 21–24 --}}
        <tr>
            <td colspan="2" class="no-pad">
                <table>
                    <tr>
                        <td style="width:57.8947%;" class="h21_24">
                            <div class="lbl">
                                <span class="num">21</span> Sastādīts
                                <span style="margin-left:10mm;">Vieta</span>
                                <span style="margin-left:18mm;">Datums</span>
                            </div>
                            <div style="margin-top:3mm;" class="lbl">
                                <span class="num">22</span> Ierašanās iekraušanai
                                <span class="sub">Ankunft für einladung</span>
                                <div style="margin-top:2mm;" class="lbl">Aizbraukšana <span class="sub">Abfahrt</span></div>
                                <div style="margin-top:2mm;" class="lbl">Nosūtītāja paraksts un zīmogs
                                    <span class="sub">Unterschrift und Stempel des Absender</span>
                                </div>
                            </div>
                        </td>

                        <td style="width:28.9474%;" class="h21_24">
                            <div class="lbl"><span class="num">23</span> Ceļazīmes Nr. ____________________</div>
                            <div style="margin-top:8mm;" class="lbl">Vadītāja uzvārdi __________________</div>
                        </td>

                        <td style="width:13.1579%;" class="h21_24 no-pad">
                            <table>
                                <tr><td class="pad-s"><div class="lbl"><span class="num">24</span> Krava saņemta</div><div class="mini right">am</div></td></tr>
                                <tr><td class="pad-s mini">Ierašanās izkraušanai</td></tr>
                                <tr><td class="pad-s mini">Ankunft für Ausladung</td></tr>
                                <tr><td class="pad-s mini">Aizbraukšana</td></tr>
                                <tr><td class="pad-s mini">Abfahrt</td></tr>
                                <tr><td class="pad-s mini right">Saņēmu</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- 25–26 --}}
        <tr>
            <td colspan="2" class="no-pad">
                <table>
                    <tr>
                        <td style="width:57.8947%;" class="h25_26">
                            <div class="lbl"><span class="num">25</span> Reģistrācijas Nr. / Amtl. Kennzeichen</div>
                            <div class="lines"><div class="line"></div><div class="line"></div></div>
                        </td>
                        <td style="width:42.1053%;" class="h25_26">
                            <div class="lbl"><span class="num">26</span> Marka / Typ &nbsp;&nbsp;&nbsp; Puspiekabe / Auflieger</div>
                            <div class="lines"><div class="line"></div><div class="line"></div></div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
</div>

</body>
</html>
