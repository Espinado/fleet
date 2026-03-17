# Vadītāja rokasgrāmata — Fleet Manager

**Krievu valodā:** [manager-manual.md](manager-manual.md)

Norādījumi darbam ar Fleet Manager tīmekļa lietotni: visi sadalījumi un galvenās darbības lietotājam ar pilnu piekļuvi.

---

## 1. Pieslēgšanās un navigācija

- Sistēmā ieiet ar **e-pastu un paroli** (kontu izveido administrators).
- Pēc pieslēgšanās atveras **galvenā lapa (Dashboard)**.
- Kreisajā pusē atrodas **sānu izvēlne**:
  - **Dashboard** — kopsavilkums par dokumentiem ar beidzamo termiņu
  - **Šoferi** — šoferu saraksts un kartītes
  - **Transports** — apakšizvēlne: **Vilcēji**, **Piekabes** (un pēc iespējas — Pārvadātāji, Karte, Apkope)
  - **Reisi un pasūtījumi** — apakšizvēlne: **Pasūtījumi**, **Reisi**
  - **Klienti** — klientu bāze (uzņēmumi)
  - **Statistika** — apakšizvēlne: **Pārskats**, **Odometra notikumi** u.c.
  - **Rēķini** — rēķinu saraksts un maksājumi
- Galvenē: lietotāja vārds un poga **Iziet**.

 Mobilajās ierīcēs izvēlne atveras ar pogu «hamburgeris» (☰).

---

## 2. Dashboard (galvenā)

Galvenajā lapā tiek rādīta **dokumentu ar beidzamo termiņu tabula** (derīguma termiņš nākamo 30 dienu laikā).

- **Objektu veidi**: šoferi, vilcēji, piekabes, kravas (TripCargo).
- **Dokumenti**:
  - **šoferiem**: vadītāja apliecība (License), Code 95, atļauja (Permit), medicīniskā izziņa (Medical), deklarācija (Declaration);
  - **vilcējiem**: tehniskā apskate (Inspection), apdrošināšana (Insurance), tehniskā pase (Tech passport);
  - **piekabēm**: tehniskā apskate, apdrošināšana, TIR, tehniskā pase;
  - **kravām** — pēc vajadzības savi termiņi.
- Pieejami **meklēšana**, **kārtošana** pēc kolonnām un **rindu skaits** lapā.
- Katrā rindā redzams: veids, nosaukums (šofera vārds/uzvārds vai TС marka un numurs u.c.), dokuments, beigu datums, atlikušo dienu skaits, uzņēmums, statuss.

Izmantojiet Dashboard, lai laikus atjaunotu šoferu un transporta dokumentus.

---

## 3. Šoferi

- **Šoferu saraksts** — tabula ar meklēšanu un kārtošanu.
- **Pievienot šoferi** — poga «Izveidot» → forma: vārds, uzvārds, kontakti, dokumenti (vadītāja apliecība, Code 95, medicīniskā izziņa u.c.), uzņēmums, PIN mobilās lietotnes pieslēgšanai (4–6 cipari).
- **Šofera kartīte** — visu datu, dokumentu un saistību apskate.
- **Rediģēšana** — datu un dokumentu maiņa.
- Pēc vajadzības kartītē/rediģēšanā iespējams **dzēst** šoferi (ja tas paredzēts saskarnē).

Šofera PIN nepieciešams viņam, lai pieslēgtos mobilajai lietotnei (sadaļa «Šoferis»).

---

## 4. Transports

### Vilcēji

- **Vilcēju saraksts** — tabula ar pamatdatiem (marka, modelis, valsts numurs, statuss, uzņēmums).
- **Pievienot vilcēju** — forma: marka, modelis, numurs, uzņēmums, dokumentu datumi (tehniskā apskate, apdrošināšana, tehniskā pase), pēc vajadzības — CAN klātbūtne (automātiskajam odometram).
- **Vilcēja kartīte** — pilni dati, dokumenti, vēsture.
- **Rediģēšana** — datu un dokumentu termiņu maiņa.

### Piekabes

- **Piekabju saraksts** — tabula (marka, numurs, tips, uzņēmums u.c.).
- **Pievienot piekabi** — tips (iesk. konteineru), marka, numurs, dokumenti (tehniskā apskate, apdrošināšana, TIR, tehniskā pase).
- **Kartīte** un **rediģēšana** — pēc analoģijas ar vilcējiem.

Transports tiek piesaistīts reisiem, izveidojot vai rediģējot reisu.

---

## 5. Klienti

- **Klientu saraksts** — uzņēmumi (klienti, sūtītāji, saņēmēji).
- **Pievienot klientu** — nosaukums, valsts, pilsēta, adrese, rekvizīti, kontakti.
- **Kartīte** un **rediģēšana** — datu apskate un maiņa.

Klienti tiek izmantoti reisos un pasūtījumos kā pasūtītāji (customer), sūtītāji (shipper) un saņēmēji (consignee).

---

## 6. Pasūtījumi (transporta pasūtījumi)

- **Pasūtījumu saraksts** (**/orders**) — transporta pasūtījumu tabula ar meklēšanu un filtriem.
- **Izveidot pasūtījumu** — forma ar pasūtījuma datiem (maršruts, kravas, klients u.c.).
- **Pasūtījuma kartīte** — pasūtījuma apskate, saistība ar reisu (ja pasūtījums jau piesaistīts reisam).

**Saistība pasūtījums ↔ reiss:**

- **Izveidot reisu no pasūtījuma** — no pasūtījuma kartītes var izveidot jaunu reisu; pasūtījums automātiski tiek piesaistīts reisam.
- **Pievienot esošam reisam** — pasūtījuma kartītē poga «Pievienot reisam» atver modālo logu ar piemērotu reisu sarakstu (neabeigti). Viens pasūtījums var būt piesaistīts tikai vienam reisam; pievienot var tikai tad, ja reiss vēl nav pabeigts. Pēc reisa izvēles un apstiprinājuma pasūtījums tiek pievienots izvēlētajam reisam (konsolidācija — vairāki pasūtījumi vienā reisā).
- **Pievienot pasūtījumu no reisa kartītes** — reisa apskatē blokā «Pasūtījumi reisā» (ja reiss nav pabeigts) pieejama viena vai vairāku pieejamo pasūtījumu izvēle un poga «Pievienot pasūtījumus reisam»; izvēlētie pasūtījumi tiek pievienoti šim reisam. Jau piesaistītu pasūtījumu var noņemt no reisa ar pogu «Noņemt no reisa».

---

## 7. Reisi

### Reisu saraksts

- **Reisi** — tabula ar filtriem un kārtošanu (pēc datuma, statusa, šofera u.c.).
- No saraksta var pāriet uz reisa **izveidi**, **apskati** vai **rediģēšanu**.

### Reisa izveide

Dati aizpilda soli pa solim:

1. **Ekspeditors** — uzņēmuma (expeditor) un bankas izvēle rekvizītiem.
2. **Pārvadātājs**:
   - savs transports (ekspeditors vai cita iekšējā uzņēmuma);
   - vai **trešā puse (third party)** — tad tiek izveidota jauna uzņēmuma, vilcējs un piekabe, reisam tiek pievienoti izdevumi apakšpārvadātājam.
3. **Transports** — šoferis, vilcējs, piekabe (vai trešās puses dati).
4. **Maršruts (soļi)** — iekraušanas un izkraušanas punkti:
   - soļa tips: iekraušana (loading) / izkraušana (unloading);
   - valsts, pilsēta, adrese, datums un laiks;
   - soļu secību var mainīt (vilkt un nomest rediģējot).
5. **TIR / muitas** — starptautiskos pārvadājumos iespējams ieslēgt muitas noformēšanu (TIR) un norādīt **TIR robežpunktu** (pārejas/muitas punkta adrese).
6. **Kravas (cargos)** — viena vai vairākas:
   - klients (customer), sūtītājs (shipper), saņēmējs (consignee);
   - frašta cena, PVN;
   - **kravas pozīcijas (items)**: apraksts, iepakojumi, paletes, svars (tīrais/bruto), tilpums, iekraušanas metri, pēc vajadzības — bīstama kravas, temperatūra, muitas kods (customs_code) u.c.;
   - kravas piesaistīšana **iekraušanas un izkraušanas soļiem** (kuri maršruta soļi attiecas uz šo kravu).
7. Konteineru reisiem — konteineru numurs un zīmogi (ja izmantoti formā).

Pēc saglabāšanas tiek izveidoti reiss, maršruta soļi, kravas un pozīcijas; ar third party — uzņēmums, vilcējs, piekabe un izdevumi. Vienā reisā var būt **vairāki pasūtījumi/kravas** (konsolidācija); pasūtījumus pievieno no sadaļas **Pasūtījumi** ar «Pievienot reisam», izveidojot reisu no pasūtījuma vai **reisa kartītē** blokā «Pasūtījumi reisā».

### Reisa apskate

Lapā tiek rādīts:

- **Galvene**: reisa numurs, statuss, shēma (savs/trešā puse), ekspeditors, pārvadātājs, transports (šoferis, vilcējs, piekabe), ar third party — fiksēta samaksa.
- **Kopsavilkuma rādītāji**: kopējais svars, tilpums, iekraušanas metri, frašts ar PVN/bez, preces vērtība, summa pēc piegādātāja rēķiniem u.c.
- **Maršruta redaktors** (akordeons «Maršruts») — **Trip Route Editor**: soļu secības maiņa vilkot, jaunās secības saglabāšana.
- **Pasūtījumi reisā** — bloks ar reisam piesaistīto pasūtījumu sarakstu: pie katra — saite uz pasūtījuma kartīti un poga «Noņemt no reisa». Ja reiss **nav pabeigts**, zemāk tiek rādīts pieejamo pasūtījumu saraksts (vēl nepiesaistīti reisam); var izvēlēties vienu vai vairākus pasūtījumus un nospiest **«Pievienot pasūtījumus reisam»** — izvēlētie pasūtījumi tiek pievienoti reisam (konsolidācija). Tātad pasūtījumus var pievienot gan no **reisa kartītes**, gan no pasūtījuma kartītes («Pievienot reisam»).
- **Kravas pēc klientiem** — kravu grupas ar detaliem datiem:
  - katrai kravai: sūtītājs → saņēmējs, iepakojumi, paletes, svars, tilpums, frašts, preces vērtība, piegādātāja rēķins;
  - ar šo kravu saistītie maršruta punkti (iekraušana/izkraušana) ar datumu un laiku.
- **Dokumenti par kravu**:
  - **CMR** — numurs, poga «Ģenerēt CMR»: tiek izveidots PDF un piesaistīts kravai;
  - **Rēķins (Invoice)** — rēķina ģenerēšana par kravu, PDF saglabāšana.
- **Izdevumi par reisu** — izdevumu saraksts (degviela, maksas ceļi, stāvvieta u.c.), pēc vajadzības pievienošana/rediģēšana no vadītāja puses (ja ieviesta saskarnē).

Šoferis izpilda reisu mobilajā lietotnē (soļu statusi, odometrs, dokumentu augšupielāde, izdevumi). Vadītājs šeit kontrolē maršrutu, kravas un dokumentus.

### Reisa rediģēšana

Tādi paši bloki kā izveidē: ekspeditors, pārvadātājs, transports, maršruta soļi, kravas un pozīcijas. Izmaiņas tiek saglabātas esošajā reisā. Soļu secību ērtāk mainīt **reisa apskatē** caur maršruta redaktoru.

---

## 8. Statistika

### Pārskats

- Tabula par reisiem izvēlētajā periodā: datumi, šoferis, vilcējs, nobraukums, izdevumi u.c.
- Filtri pēc perioda (datums no/līdz, ātrie diapazoni), meklēšana, kārtošana.
- Izmanto reisu un nobraukuma analīzei.

### Odometra notikumi

- Odometra notikumu tabula: izbraukšana/atgriešanās garāžā, notikumi pēc maršruta soļiem, šofera izdevumi (degviela, AdBlue) ar odometra piesaisti.
- Filtri: notikuma tips, šoferis, vilcējs, periods (datums no/līdz).
- Noderīgi nobraukuma un izdevumu piesaistes kilometrāžai pārbaudei.

*Ieteikumi statistikas paplašināšanai (izmaksas uz km, debitoru parādi, top klienti, parka izmantošana, l/100 km u.c.) un lielo loģistikas uzņēmumu prakses — dokumentā [stats-recommendations.md](stats-recommendations.md).*

---

## 9. Rēķini (Invoices)

- **Rēķinu saraksts** — tabula ar numuriem, datumiem, summām, maksājuma statusu (apmaksāts/daļēji/neapmaksāts).
- Filtri: meklēšana, statuss (visi / apmaksāts / daļēji / neapmaksāts), kārtošana.
- **Atvērt PDF** — ģenerētā rēķina apskate vai lejupielāde.
- **Ievadīt maksājumu** — maksājuma datums un summa izvēlētajam rēķinam; atlikuma summa tiek pārrēķināta.

Rēķini par kravām tiek izveidoti no reisa kartītes (rēķina ģenerēšanas poga par kravu).

---

## 10. CMR un rēķini par reisu

- **CMR** tiek ģenerēts **reisa apskatē** katrai kravai: poga «Ģenerēt CMR» → tiek izveidots PDF, saglabāts un piesaistīts kravai. CMR numuru var iestatīt/rediģēt formā.
- **Rēķins (Invoice)** par kravu ģenerēts turpat: rēķina ģenerēšanas poga → tiek izveidots rēķina PDF, ieraksts parādās sadaļā **Rēķini**. Tālāka darbība ar apmaksu — sadaļā «Rēķini».

---

## 11. Profils un iziešana

- **Profils** — savu datu apskate un pēc vajadzības maiņa (ja ieviesta).
- **Iziet** — poga izvēlnē galvenē labajā pusē; pabeidz sesiju un novirza uz pieslēgšanās lapu.

---

## Īsa atgādinājuma tabula pa sadalījumiem

| Sadalījums | Galvenās darbības |
|------------|-------------------|
| **Dashboard** | Dokumentu ar beidzamo termiņu kontrole šoferiem, vilcējiem, piekabēm |
| **Šoferi** | Saraksts, izveide, kartīte, rediģēšana, PIN lietotnei |
| **Vilcēji** | Saraksts, izveide, kartīte, rediģēšana, dokumenti, CAN |
| **Piekabes** | Saraksts, izveide, kartīte, rediģēšana, tips (iesk. konteineru) |
| **Klienti** | Saraksts, izveide, kartīte, uzņēmumu rediģēšana |
| **Pasūtījumi** | Pasūtījumu saraksts; reisa izveide no pasūtījuma; pievienošana reisam no pasūtījuma vai reisa kartītes (bloks «Pasūtījumi reisā») |
| **Reisi** | Saraksts, izveide, apskate (maršruts, **pasūtījumi reisā** — pievienot/noņemt pasūtījumus, kravas, CMR, rēķini, izdevumi), rediģēšana; vairāki pasūtījumi vienā reisā |
| **Statistika → Pārskats** | Reisi par periodu, nobraukums, izdevumi |
| **Statistika → Notikumi** | Odometra notikumi, filtri pēc šofera/vilcēja/veida/datumam |
| **Rēķini** | Rēķinu saraksts, PDF atvēršana, maksājumu ievade |
| **Reisā** | Maršruta redaktors (soļu secība), bloks «Pasūtījumi reisā» (pievienot/noņemt pasūtījumus), CMR un rēķina ģenerēšana par kravu |

---

## Saikne ar šofera lietotni

- Šoferis pieslēdzas **mobilajai lietotnei** ar **PIN** (tiek norādīts šofera kartītē).
- Lietotnē šoferis: atzīmē izbraukšanu/atgriešanos garāžā ar odometru, maina soļu statusus (iekraušana/izkraušana), augšupielādē dokumentus par soļiem, pievieno izdevumus par reisu.
- Vadītājs tīmekļa saskarnē redz reisus, maršrutu, kravas, ģenerētos CMR un rēķinus, izdevumus un odometra notikumus statistikā. Pēc vajadzības maršrutu var koriģēt (maršruta redaktors reisa apskatē).

---

*Rokasgrāmatas versija: 1.1. Fleet Manager — vadītāja tīmekļa kabinets. Pievienota sadaļa «Pasūtījumi» un pasūtījumu saistība ar reisiem (reisa izveide no pasūtījuma, pasūtījuma pievienošana esošam reisam, konsolidācija).*
