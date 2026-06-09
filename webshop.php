<?php

/* Pokrecem sesiju kako bih pamtila je li admin ulogiran */
session_start();

/* Spajam se na bazu i kreiram tablicu proizvodi ako ne postoji */
try {
    $baza = new PDO('sqlite:linum.db');
    $baza->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $baza->exec("CREATE TABLE IF NOT EXISTS proizvodi (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        naziv TEXT NOT NULL,
        opis TEXT,
        cijena REAL NOT NULL,
        slika TEXT,
        kategorija TEXT
    )");
} catch (Exception $e) {
    die("Greska pri spajanju na bazu: " . $e->getMessage());
}


/* Lozinka za admin pristup */
$lozinka_admin = 'linumhr2026';


/* Obrada prijave admina */
if (isset($_POST['prijava'])) {
    if ($_POST['lozinka'] === $lozinka_admin) {
        $_SESSION['admin'] = true;
    } else {
        $greska_prijave = 'Pogresna lozinka.';
    }
}


/* Obrada odjave admina */
if (isset($_GET['odjava'])) {
    $_SESSION['admin'] = false;
    header("Location: webshop.php");
    exit();
}


/* Provjeravam je li admin trenutno ulogiran */
$je_admin = isset($_SESSION['admin']) && $_SESSION['admin'] === true;


/* Obrada dodavanja novog proizvoda u bazu */
if ($je_admin && isset($_POST['dodaj'])) {
    $unos = $baza->prepare("INSERT INTO proizvodi (naziv, opis, cijena, slika, kategorija)
                            VALUES (:naziv, :opis, :cijena, :slika, :kategorija)");
    $unos->execute([
        ':naziv' => trim($_POST['naziv']),
        ':opis' => trim($_POST['opis']),
        ':cijena' => floatval($_POST['cijena']),
        ':slika' => trim($_POST['slika']),
        ':kategorija' => $_POST['kategorija']
    ]);
    header("Location: webshop.php#admin");
    exit();
}


/* Obrada brisanja proizvoda iz baze */
if ($je_admin && isset($_GET['obrisi'])) {
    $brisanje = $baza->prepare("DELETE FROM proizvodi WHERE id = :id");
    $brisanje->execute([':id' => intval($_GET['obrisi'])]);
    header("Location: webshop.php#admin");
    exit();
}


/* Citam sve proizvode iz baze */
$citanje = $baza->query("SELECT * FROM proizvodi ORDER BY id DESC");
$proizvodi_iz_baze = $citanje->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="hr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title>Webshop | Linum.hr | Posteljine, jastučnice, plahte i deke</title>
    <meta name="description"
        content="Pregledajte cijelu ponudu krevetnine na Linum.hr. Posteljine, plahte, jastučnice i deke od pamuka uz brzu dostavu i 30 dana povrata.">
    <meta name="keywords"
        content="webshop, krevetnina, posteljina, plahte, deke, jastučnice, pamuk, online kupovina, Hrvatska, Linum">
    <meta name="author" content="Helena Ćiško">

    <!-- Noto Serif Display -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Serif+Display:ital,wght@0,100..900;1,100..900&display=swap');
    </style>

    <!-- Raleway -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap');
    </style>

    <!-- Phosphor Icons -->
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css">

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Zaglavlje -->
    <header class="zaglavlje">
        <nav class="nav-unutra">

            <a href="index.html" class="logo-veza">
                <img src="img/logo.png" alt="Linum.hr logo" class="logo-slika" width="40" height="40">
                <span class="logo-naziv">Linum.hr</span>
            </a>

            <ul class="nav-veze">
                <li><a href="index.html">Početna</a></li>
                <li><a href="webshop.php" class="aktivna">Webshop</a></li>
                <li><a href="galerija.html">Galerija</a></li>
                <li><a href="kontakt.html">Kontakt</a></li>
                <li><a href="pitanja.html">FAQ</a></li>
            </ul>

            <div class="nav-desno">
                <button class="kosara-gumb" type="button">
                    <i class="ph ph-shopping-cart"></i>
                    <span class="kosara-broj">0</span>
                </button>

                <button class="hamburger" type="button">
                    <i class="ph ph-list hamburger-list"></i>
                    <i class="ph ph-x hamburger-x"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Mobilni izbornik -->
    <div class="mob-izbornik" id="mob-izbornik">
        <div class="mob-nav-zaglavlje">
            <button class="mob-zatvori" type="button">
                <i class="ph ph-x"></i>
            </button>

            <a href="index.html" class="logo-veza">
                <img src="img/logo.png" alt="Linum.hr logo" class="logo-slika" width="36" height="36">
                <span class="logo-naziv">Linum.hr</span>
            </a>

            <button class="kosara-gumb" type="button">
                <i class="ph ph-shopping-cart"></i>
                <span class="kosara-broj">0</span>
            </button>
        </div>

        <nav>
            <ul class="mob-veze">
                <li><a href="index.html">Početna</a></li>
                <li><a href="webshop.php">Webshop</a></li>
                <li><a href="galerija.html">Galerija</a></li>
                <li><a href="kontakt.html">Kontakt</a></li>
                <li><a href="pitanja.html">FAQ</a></li>
            </ul>
        </nav>
    </div>


    <main>

        <!-- Webshop sekcija -->
        <section class="shop-sekcija">

            <!-- Zaglavlje sekcije s filterom -->
            <div class="shop-zaglavlje">
                <h1 class="shop-naslov">Webshop</h1>

                <select class="shop-filter" id="shop-filter">
                    <option value="sve">Sve</option>
                    <option value="deke">Deke</option>
                    <option value="plahte">Plahte</option>
                    <option value="posteljine">Posteljine</option>
                    <option value="jastucnice">Jastučnice</option>
                </select>
            </div>

            <!-- Mreža proizvoda -->
            <div class="shop-mreza">

                <!-- 1. red: Posteljine -->

                <!-- SALE -->
                <article class="kartica shop-kartica" data-kategorija="posteljine">
                    <div class="kartica-slika-omotac">
                        <img src="img/posteljina4.png" alt="Posteljina set Medvjedići" loading="lazy">
                        <span class="oznaka-sale">SALE</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Posteljina set "Medvjedići"</h3>
                        <p class="kartica-opis">Dječji set od hipoalergenskog pamuka s motivom medvjedića u žutoj boji. Dimenzije: navlaka 140 x 200 cm, jastučnica 60 x 80 cm.</p>
                        <div class="kartica-dno">
                            <div class="kartica-cijena">
                                <span class="cijena-stara">44,99 €</span>
                                <span class="cijena-nova">31,99 €</span>
                            </div>
                            <button class="gumb-dodaj" type="button" data-id="posteljina-medvjedici"
                                data-naziv='Posteljina set "Medvjedići"' data-opis="100% pamuk, 140x200 cm"
                                data-cijena="31.99" data-slika="img/posteljina4.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- Bez oznake -->
                <article class="kartica shop-kartica" data-kategorija="posteljine">
                    <div class="kartica-slika-omotac">
                        <img src="img/posteljina5.png" alt="Posteljina set Lavanda" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Posteljina set "Lavanda"</h3>
                        <p class="kartica-opis">Set od češljanog pamuka s uzorkom lavande u ljubičastoj boji. Dimenzije: navlaka 200 x 220 cm, jastučnice 60 x 80 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">49,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="posteljina-lavanda"
                                data-naziv='Posteljina set "Lavanda"' data-opis="100% pamuk, 200x220 cm"
                                data-cijena="49.99" data-slika="img/posteljina5.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="posteljine">
                    <div class="kartica-slika-omotac">
                        <img src="img/posteljina3.png" alt="Posteljina set Magla" loading="lazy">
                        <span class="oznaka-novo">NOVO</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Posteljina set "Magla"</h3>
                        <p class="kartica-opis">Set od češljanog pamuka u nježno zelenoj boji s cvjetnim motivima. Dimenzije: navlaka 200 x 220 cm, jastučnice 60 x 80 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">42,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="posteljina-magla"
                                data-naziv='Posteljina set "Magla"' data-opis="100% pamuk, 200x220 cm"
                                data-cijena="42.99" data-slika="img/posteljina3.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="posteljine">
                    <div class="kartica-slika-omotac">
                        <img src="img/posteljina2.png" alt="Posteljina set Rosé" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Posteljina set "Rosé"</h3>
                        <p class="kartica-opis">Set od muslin pamuka u nježnoj rozoj boji s nabranom teksturom. Dimenzije: navlaka 200 x 220 cm, jastučnice 60 x 80 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">54,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="posteljina-rose"
                                data-naziv='Posteljina set "Rosé"' data-opis="Muslin pamuk, 200x220 cm"
                                data-cijena="54.99" data-slika="img/posteljina2.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- 2. red: Plahte -->

                <!-- SALE -->
                <article class="kartica shop-kartica" data-kategorija="plahte">
                    <div class="kartica-slika-omotac">
                        <img src="img/plahta5.png" alt="Plahta Tulipani" loading="lazy">
                        <span class="oznaka-sale">SALE</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Plahta "Tulipani"</h3>
                        <p class="kartica-opis">Pamučna plahta s uzorkom tulipana na nježnoj podlozi. Dimenzije: 160 x 200 cm.</p>
                        <div class="kartica-dno">
                            <div class="kartica-cijena">
                                <span class="cijena-stara">29,99 €</span>
                                <span class="cijena-nova">19,99 €</span>
                            </div>
                            <button class="gumb-dodaj" type="button" data-id="plahta-tulipani"
                                data-naziv='Plahta "Tulipani"' data-opis="100% pamuk, 160x200 cm" data-cijena="19.99"
                                data-slika="img/plahta5.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- Bez oznake -->
                <article class="kartica shop-kartica" data-kategorija="plahte">
                    <div class="kartica-slika-omotac">
                        <img src="img/plahta2.png" alt="Plahta Pruge" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Plahta "Pruge"</h3>
                        <p class="kartica-opis">Pamučna plahta s plavim prugama na bijeloj podlozi. Dimenzije: 180 x 200 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">24,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="plahta-pruge" data-naziv='Plahta "Pruge"'
                                data-opis="100% pamuk, 180x200 cm" data-cijena="24.99" data-slika="img/plahta2.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="plahte">
                    <div class="kartica-slika-omotac">
                        <img src="img/plahta3.png" alt="Plahta Oblaci" loading="lazy">
                        <span class="oznaka-novo">NOVO</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Plahta "Oblaci"</h3>
                        <p class="kartica-opis">Dječja pamučna plahta s motivima oblaka i mjeseca u nježno ružičastoj boji. Dimenzije: 140 x 200 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">22,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="plahta-oblaci"
                                data-naziv='Plahta "Oblaci"' data-opis="100% pamuk, 140x200 cm" data-cijena="22.99"
                                data-slika="img/plahta3.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="plahte">
                    <div class="kartica-slika-omotac">
                        <img src="img/plahta4.png" alt="Plahta Zvjezdice" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Plahta "Zvjezdice"</h3>
                        <p class="kartica-opis">Dječja pamučna plahta s motivima oblaka i zvjezdica u nježno zelenoj boji. Dimenzije: 140 x 200 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">26,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="plahta-zvjezdice"
                                data-naziv='Plahta "Zvjezdice"' data-opis="Poliester saten, 140x200 cm"
                                data-cijena="26.99" data-slika="img/plahta4.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- 3. red: Jastučnice -->

                <!-- SALE -->
                <article class="kartica shop-kartica" data-kategorija="jastucnice">
                    <div class="kartica-slika-omotac">
                        <img src="img/jastucnica1.png" alt="Jastučnica Maslina" loading="lazy">
                        <span class="oznaka-sale">SALE</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Jastučnica "Maslina"</h3>
                        <p class="kartica-opis">Set od dvije satenske jastučnice u maslinasto zelenoj boji s cvjetnim uzorkom. Dimenzije: 50 x 70 cm.</p>
                        <div class="kartica-dno">
                            <div class="kartica-cijena">
                                <span class="cijena-stara">24,99 €</span>
                                <span class="cijena-nova">16,99 €</span>
                            </div>
                            <button class="gumb-dodaj" type="button" data-id="jastucnica-maslina"
                                data-naziv='Jastučnica "Maslina"' data-opis="Poliester saten, 50x70 cm, 2 kom"
                                data-cijena="16.99" data-slika="img/jastucnica1.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- Bez oznake -->
                <article class="kartica shop-kartica" data-kategorija="jastucnice">
                    <div class="kartica-slika-omotac">
                        <img src="img/jastucnica3.png" alt="Jastučnica Karamela" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Jastučnica "Karamela"</h3>
                        <p class="kartica-opis">Set od dvije mekane jastučnice u toploj karamel boji s pliš teksturom. Dimenzije: 50 x 70 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">19,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="jastucnica-karamela"
                                data-naziv='Jastučnica "Karamela"' data-opis="Pliš, 50x70 cm, 2 kom" data-cijena="19.99"
                                data-slika="img/jastucnica3.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="jastucnice">
                    <div class="kartica-slika-omotac">
                        <img src="img/jastucnica5.png" alt="Jastučnica Sofija" loading="lazy">
                        <span class="oznaka-novo">NOVO</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Jastučnica "Sofija"</h3>
                        <p class="kartica-opis">Set od dvije pamučne jastučnice s klasičnim kariranim uzorkom u sivim tonovima. Dimenzije: 60 x 80 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">17,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="jastucnica-karo"
                                data-naziv='Jastučnica "Sofija"' data-opis="100% pamuk, 60x80 cm, 2 kom"
                                data-cijena="17.99" data-slika="img/jastucnica5.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- Bez oznake -->
                <article class="kartica shop-kartica" data-kategorija="jastucnice">
                    <div class="kartica-slika-omotac">
                        <img src="img/jastucnica6.png" alt="Jastučnica Botanik" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Jastučnica "Botanik"</h3>
                        <p class="kartica-opis">Set od dvije jastučnice s botaničkim uzorkom lišća na zelenkastoj podlozi. Dimenzije: 50 x 70 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">22,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="jastucnica-botanik"
                                data-naziv='Jastučnica "Botanik"' data-opis="100% pamuk, 50x70 cm, 2 kom"
                                data-cijena="22.99" data-slika="img/jastucnica6.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- 4. red: Deke -->

                <!-- SALE -->
                <article class="kartica shop-kartica" data-kategorija="deke">
                    <div class="kartica-slika-omotac">
                        <img src="img/dekica2.png" alt="Deka Leopard" loading="lazy">
                        <span class="oznaka-sale">SALE</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Deka "Leopard"</h3>
                        <p class="kartica-opis">Mekana deka od mikroflisa s leopard uzorkom u bež i smeđim tonovima. Dimenzije: 130 x 170 cm.</p>
                        <div class="kartica-dno">
                            <div class="kartica-cijena">
                                <span class="cijena-stara">39,99 €</span>
                                <span class="cijena-nova">27,99 €</span>
                            </div>
                            <button class="gumb-dodaj" type="button" data-id="deka-leopard" data-naziv='Deka "Leopard"'
                                data-opis="Mikrofleece, 130x170 cm" data-cijena="27.99" data-slika="img/dekica2.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- Bez oznake -->
                <article class="kartica shop-kartica" data-kategorija="deke">
                    <div class="kartica-slika-omotac">
                        <img src="img/dekica5.png" alt="Deka Ljubičica" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Deka "Ljubičica"</h3>
                        <p class="kartica-opis">Mekana deka od mikrovlakana u čistoj ljubičastoj boji. Dimenzije: 150 x 200 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">34,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="deka-ljubicica"
                                data-naziv='Deka "Ljubičica"' data-opis="Mikrovlakna, 150x200 cm" data-cijena="34.99"
                                data-slika="img/dekica5.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="deke">
                    <div class="kartica-slika-omotac">
                        <img src="img/dekica9.png" alt="Deka Koralj" loading="lazy">
                        <span class="oznaka-novo">NOVO</span>
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Deka "Koralj"</h3>
                        <p class="kartica-opis">Lagana muslin deka s motivima koralja i školjki u plavim tonovima. Dimenzije: 120 x 160 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">29,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="deka-koralj" data-naziv='Deka "Koralj"'
                                data-opis="Muslin, 120x160 cm" data-cijena="29.99" data-slika="img/dekica9.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>

                <!-- NOVO -->
                <article class="kartica shop-kartica" data-kategorija="deke">
                    <div class="kartica-slika-omotac">
                        <img src="img/dekica7.png" alt="Deka Mačkice" loading="lazy">
                    </div>
                    <div class="kartica-info">
                        <h3 class="kartica-naziv">Deka "Mačkice"</h3>
                        <p class="kartica-opis">Mekana flis deka s uzorkom mačkica i šapica na sivoj podlozi. Dimenzije: 130 x 170 cm.</p>
                        <div class="kartica-dno">
                            <span class="cijena-redovna">32,99 €</span>
                            <button class="gumb-dodaj" type="button" data-id="deka-mackice" data-naziv='Deka "Mačkice"'
                                data-opis="Flis, 130x170 cm" data-cijena="32.99" data-slika="img/dekica7.png">
                                <i class="ph ph-plus"></i>
                            </button>
                        </div>
                    </div>
                </article>


                <!-- Proizvodi iz baze podataka -->
                <?php foreach ($proizvodi_iz_baze as $p): ?>
                    <article class="kartica shop-kartica" data-kategorija="<?= htmlspecialchars($p['kategorija']) ?>">
                        <div class="kartica-slika-omotac">
                            <img src="<?= htmlspecialchars($p['slika']) ?>" alt="<?= htmlspecialchars($p['naziv']) ?>" loading="lazy">
                        </div>
                        <div class="kartica-info">
                            <h3 class="kartica-naziv"><?= htmlspecialchars($p['naziv']) ?></h3>
                            <p class="kartica-opis"><?= htmlspecialchars($p['opis']) ?></p>
                            <div class="kartica-dno">
                                <span class="cijena-redovna"><?= number_format($p['cijena'], 2, ',', '.') ?> €</span>
                                <button class="gumb-dodaj" type="button"
                                    data-id="baza-<?= $p['id'] ?>"
                                    data-naziv="<?= htmlspecialchars($p['naziv']) ?>"
                                    data-opis="<?= htmlspecialchars($p['opis']) ?>"
                                    data-cijena="<?= $p['cijena'] ?>"
                                    data-slika="<?= htmlspecialchars($p['slika']) ?>">
                                    <i class="ph ph-plus"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

            </div>

        </section>


        <!-- Admin sekcija -->
        <section class="admin-sekcija" id="admin">

            <?php if (!$je_admin): ?>

                <!-- Forma za prijavu admina -->
                <div class="admin-prijava-okvir">
                    <div class="admin-prijava">
                        <h2 class="admin-naslov-mali">Administratorska prijava</h2>
                        <form method="POST" class="admin-forma-prijava">
                            <input type="password" name="lozinka" placeholder="Unesite lozinku..." required>
                            <button type="submit" name="prijava">Prijava</button>
                        </form>
                    </div>
                    <?php if (isset($greska_prijave)): ?>
                        <p class="admin-greska"><?= $greska_prijave ?></p>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <!-- Admin panel kad je ulogiran -->
                <div class="admin-panel">

                    <div class="admin-zaglavlje">
                        <h2 class="admin-naslov">Administratorski panel</h2>
                        <a href="?odjava=1" class="admin-odjava">Odjava</a>
                    </div>

                    <!-- Forma za dodavanje novog proizvoda -->
                    <form method="POST" class="admin-forma-dodavanje">
                        <h3 class="admin-podnaslov">Novi proizvod</h3>

                        <div class="admin-red">
                            <div class="admin-polje">
                                <label for="naziv">Naziv</label>
                                <input type="text" id="naziv" name="naziv" placeholder="npr. Deka Zebra" required>
                            </div>
                            <div class="admin-polje">
                                <label for="cijena">Cijena (€)</label>
                                <input type="number" step="0.01" id="cijena" name="cijena" placeholder="npr. 29.99" required>
                            </div>
                        </div>

                        <div class="admin-polje">
                            <label for="opis">Opis</label>
                            <textarea id="opis" name="opis" placeholder="Kratak opis proizvoda" required></textarea>
                        </div>

                        <div class="admin-red">
                            <div class="admin-polje">
                                <label for="slika">Putanja do slike</label>
                                <input type="text" id="slika" name="slika" placeholder="img/nova-slika.png" required>
                            </div>
                            <div class="admin-polje">
                                <label for="kategorija">Kategorija</label>
                                <select id="kategorija" name="kategorija" required>
                                    <option value="deke">Deke</option>
                                    <option value="plahte">Plahte</option>
                                    <option value="posteljine">Posteljine</option>
                                    <option value="jastucnice">Jastučnice</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="dodaj" class="admin-gumb">Dodaj proizvod</button>
                    </form>

                    <!-- Lista proizvoda iz baze s mogucnoscu brisanja -->
                    <div class="admin-lista">
                        <h3 class="admin-podnaslov">Proizvodi u bazi</h3>

                        <?php if (count($proizvodi_iz_baze) === 0): ?>
                            <p class="admin-prazno">Baza je trenutno prazna. Dodajte proizvod iznad.</p>
                        <?php else: ?>
                            <?php foreach ($proizvodi_iz_baze as $p): ?>
                                <div class="admin-stavka">
                                    <img src="<?= htmlspecialchars($p['slika']) ?>" alt="<?= htmlspecialchars($p['naziv']) ?>" class="admin-stavka-slika">
                                    <div class="admin-stavka-info">
                                        <strong><?= htmlspecialchars($p['naziv']) ?></strong>
                                        <span class="admin-stavka-cijena"><?= number_format($p['cijena'], 2, ',', '.') ?> €</span>
                                    </div>
                                    <a href="?obrisi=<?= $p['id'] ?>" class="admin-obrisi"
                                        onclick="return confirm('Sigurno želite obrisati ovaj proizvod?');">
                                        <i class="ph ph-trash"></i> Obriši
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endif; ?>

        </section>

    </main>


    <!-- Podnožje -->
    <footer class="podnozje">

        <div class="podnozje-logo-red">
            <img src="img/logo.png" alt="Linum.hr logo" class="podnozje-logo-slika" width="48" height="48">
            <span class="podnozje-logo-naziv">Linum.hr</span>
        </div>

        <div class="podnozje-drustvene">
            <a href="https://www.instagram.com/" class="drustvena-veza" target="_blank" rel="noopener"><i
                    class="ph ph-instagram-logo"></i></a>
            <a href="https://www.facebook.com/" class="drustvena-veza" target="_blank" rel="noopener"><i
                    class="ph ph-facebook-logo"></i></a>
            <a href="https://www.tiktok.com/" class="drustvena-veza" target="_blank" rel="noopener"><i
                    class="ph ph-tiktok-logo"></i></a>
            <a href="https://www.pinterest.com/" class="drustvena-veza" target="_blank" rel="noopener"><i
                    class="ph ph-pinterest-logo"></i></a>
        </div>

        <div class="podnozje-veze">
            <div class="podnozje-stupac">
                <h3 class="podnozje-stupac-naslov">Navigacija</h3>
                <ul>
                    <li><a href="index.html">Početna</a></li>
                    <li><a href="webshop.php">Webshop</a></li>
                    <li><a href="galerija.html">Galerija</a></li>
                    <li><a href="kontakt.html">Kontakt</a></li>
                    <li><a href="pitanja.html">FAQ</a></li>
                </ul>
            </div>
            <div class="podnozje-stupac">
                <h3 class="podnozje-stupac-naslov">Informacije</h3>
                <ul>
                    <li><a href="kontakt.html">Nešto o nama</a></li>
                    <li><a href="index.html#recenzije">Recenzije kupaca</a></li>
                    <li><a href="pitanja.html">Dostava i plaćanje</a></li>
                    <li><a href="pitanja.html">Povrat i reklamacije</a></li>
                    <li><a href="pitanja.html">Garancija kvalitete</a></li>
                </ul>
            </div>
            <div class="podnozje-stupac">
                <h3 class="podnozje-stupac-naslov">Formalno</h3>
                <ul>
                    <li><a href="#">Uvjeti korištenja</a></li>
                    <li><a href="#">Politika kolačića</a></li>
                    <li><a href="#">Politika privatnosti</a></li>
                    <li><a href="#">Izjava o pristupačnosti</a></li>
                </ul>
            </div>
            <div class="podnozje-stupac">
                <h3 class="podnozje-stupac-naslov">Kontakt</h3>
                <div class="kontakt-red"><i class="ph ph-envelope"></i><span>info@linum.hr</span></div>
                <div class="kontakt-red"><i class="ph ph-phone"></i><span>+385 91 234 5678</span></div>
                <div class="kontakt-red"><i class="ph ph-clock"></i><span>Pon - Sub: 10:00 - 21:00</span></div>
                <div class="kontakt-red"><i class="ph ph-map-pin"></i><span>Portanova, Svilajska 31A, 31000
                        Osijek</span></div>
            </div>
        </div>

        <div class="copyright-traka">
            <p>&copy; 2026 Linum.hr. Sva prava pridržana.</p>
        </div>
    </footer>


    <!-- Košarica -->
    <div class="kosara-overlay"></div>

    <aside class="kosara-panel">
        <div class="kosara-zaglavlje">
            <h2 class="kosara-naslov">Košarica</h2>
            <button class="kosara-zatvori" type="button">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div class="kosara-stavke">
            <div class="kosara-prazna">
                <i class="ph ph-shopping-cart" style="font-size: 3rem; opacity: 0.3;"></i>
                <p>Košarica je prazna</p>
            </div>
        </div>

        <div class="kosara-dno">
            <div class="kosara-redak">
                <span class="kosara-redak-naslov">Međuzbroj:</span>
                <span class="kosara-redak-vrijednost kosara-subtotal-iznos">0,00 €</span>
            </div>
            <div class="kosara-redak">
                <span class="kosara-redak-naslov">Dostava:</span>
                <span class="kosara-redak-vrijednost kosara-dostava-iznos">3,00 €</span>
            </div>
            <p class="kosara-poruka-dostava">Još 50,00 € do besplatne dostave</p>
            <div class="kosara-separator"></div>
            <div class="kosara-ukupno-red">
                <span class="kosara-ukupno-naslov">Ukupno:</span>
                <span class="kosara-ukupno-iznos">0,00 €</span>
            </div>
            <a href="naplata.html" class="kosara-checkout-gumb">Pošalji na naplatu</a>
        </div>
    </aside>


    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="jvs/script.js"></script>

</body>
</html>