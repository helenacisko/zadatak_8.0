/* Cekam da se DOM ucita */
$(document).ready(function () {


    /* Hero slider */

    var slajdovi = $('.slajd');
    var tocke = $('.tocka');
    var trenutni = 0;
    var ukupno = slajdovi.length;
    var autoInterval;

    function prikaziSlajd(indeks) {
        if (indeks >= ukupno) {
            indeks = 0;
        } else if (indeks < 0) {
            indeks = ukupno - 1;
        }

        slajdovi.removeClass('aktivni');
        tocke.removeClass('aktivna');
        $(slajdovi[indeks]).addClass('aktivni');
        $(tocke[indeks]).addClass('aktivna');
        trenutni = indeks;
    }

    function sljedeci() {
        if (trenutni + 1 >= ukupno) {
            prikaziSlajd(0);
        } else {
            prikaziSlajd(trenutni + 1);
        }
    }

    function prethodni() {
        if (trenutni - 1 < 0) {
            prikaziSlajd(ukupno - 1);
        } else {
            prikaziSlajd(trenutni - 1);
        }
    }

    function pokreniAuto() {
        clearInterval(autoInterval);
        autoInterval = setInterval(sljedeci, 5000);
    }

    $('.strelica-desno').click(function () { sljedeci(); pokreniAuto(); });
    $('.strelica-lijevo').click(function () { prethodni(); pokreniAuto(); });

    $('.tocka').click(function () {
        var odabrani = $(this).data('slajd');
        prikaziSlajd(odabrani);
        pokreniAuto();
    });

    /* Swipe na mobitelu */
    var dodirnutoX = 0;

    $('.slider-okvir').on('touchstart', function (e) {
        dodirnutoX = e.originalEvent.changedTouches[0].screenX;
    });

    $('.slider-okvir').on('touchend', function (e) {
        var razlika = dodirnutoX - e.originalEvent.changedTouches[0].screenX;
        if (razlika > 50) {
            sljedeci();
            pokreniAuto();
        } else if (razlika < -50) {
            prethodni();
            pokreniAuto();
        }
    });

    pokreniAuto();


    /* Hamburger izbornik */

    $('.hamburger').click(function () {
        if ($('.hamburger').hasClass('aktivan')) {
            /* Zatvaranje */
            $('.mob-izbornik').fadeOut(300);
            $('.hamburger').removeClass('aktivan');
            $('body').css('overflow', '');
        } else {
            /* Otvaranje */
            $('.mob-izbornik').fadeIn(300);
            $('.hamburger').addClass('aktivan');
            $('body').css('overflow', 'hidden');
        }
    });

    $('.mob-zatvori').click(function () {
        $('.mob-izbornik').fadeOut(300);
        $('.hamburger').removeClass('aktivan');
        $('body').css('overflow', '');
    });

    $('.mob-veze a').click(function () {
        $('.mob-izbornik').fadeOut(300);
        $('.hamburger').removeClass('aktivan');
        $('body').css('overflow', '');
    });

    /* Zatvori meni kad se prozor proširi iznad mobilnog breakpointa */
    $(window).resize(function () {
        if ($(window).width() > 960) {
            $('.mob-izbornik').hide();
            $('.hamburger').removeClass('aktivan');
            $('body').css('overflow', '');
        }
    });


    /* Kosarica - localStorage */

    var KLJUC = 'linum_kosara';

    function ucitajKosaru() {
        try { return JSON.parse(localStorage.getItem(KLJUC)) || []; }
        catch (e) { return []; }
    }

    function spremiKosaru(kosara) {
        localStorage.setItem(KLJUC, JSON.stringify(kosara));
    }

    function formatirajCijenu(iznos) {
        return iznos.toFixed(2).replace('.', ',') + ' €';
    }

    /* Dodajem proizvod u kosaricu */
    function dodajProizvod(id, naziv, opis, cijena, slika) {
        var kosara = ucitajKosaru();
        var pronadjen = false;

        for (var i = 0; i < kosara.length; i++) {
            if (kosara[i].id === id) {
                kosara[i].kolicina += 1;
                pronadjen = true;
                break;
            }
        }

        if (!pronadjen) {
            kosara.push({
                id: id,
                naziv: naziv,
                opis: opis,
                cijena: parseFloat(cijena),
                slika: slika,
                kolicina: 1
            });
        }

        spremiKosaru(kosara);
        osvjeziKosaru();
    }

    /* Mijenjam kolicinu ili brisem stavku */
    function promijeniKolicinu(id, promjena) {
        var kosara = ucitajKosaru();

        for (var i = 0; i < kosara.length; i++) {
            if (kosara[i].id === id) {
                kosara[i].kolicina += promjena;
                if (kosara[i].kolicina <= 0) {
                    kosara.splice(i, 1);
                }
                break;
            }
        }

        spremiKosaru(kosara);
        osvjeziKosaru();
    }

    /* Renderiram stavke u panelu */
    function renderStavke(kosara) {
        var $lista = $('.kosara-stavke');

        if (kosara.length === 0) {
            $lista.html(
                '<div class="kosara-prazna">' +
                '<i class="ph ph-shopping-cart" style="font-size:3rem;opacity:0.3;"></i>' +
                '<p>Košarica je prazna</p></div>'
            );
            return;
        }

        var html = '';
        for (var i = 0; i < kosara.length; i++) {
            var stavka = kosara[i];
            var ukupnoCijena = stavka.cijena * stavka.kolicina;
            html +=
                '<div class="kosara-stavka">' +
                '<img class="stavka-slika" src="' + stavka.slika + '" alt="' + stavka.naziv + '">' +
                '<div class="stavka-podaci">' +
                '<p class="stavka-naziv">' + stavka.naziv + '</p>' +
                '<div class="stavka-kolicina-red">' +
                '<div class="kolicina-kontrole">' +
                '<button class="kolicina-gumb" data-id="' + stavka.id + '" data-akcija="smanji"><i class="ph ph-minus"></i></button>' +
                '<span class="kolicina-broj">' + stavka.kolicina + '</span>' +
                '<button class="kolicina-gumb" data-id="' + stavka.id + '" data-akcija="povecaj"><i class="ph ph-plus"></i></button>' +
                '</div>' +
                '<span class="stavka-cijena">' + formatirajCijenu(ukupnoCijena) + '</span>' +
                '</div></div></div>';
        }

        $lista.html(html);

        /* Klikom na +/- mijenjam kolicinu */
        $('.kolicina-gumb').click(function () {
            var id = $(this).data('id');
            var akcija = $(this).data('akcija');
            if (akcija === 'povecaj') {
                promijeniKolicinu(id, 1);
            } else {
                promijeniKolicinu(id, -1);
            }
        });
    }

    /* Osvjezavam badge i iznose */
    function osvjeziKosaru() {
        var kosara = ucitajKosaru();
        var kolicina = 0;
        var cijena = 0;

        for (var i = 0; i < kosara.length; i++) {
            kolicina += kosara[i].kolicina;
            cijena += kosara[i].cijena * kosara[i].kolicina;
        }

        /* Badge */
        $('.kosara-broj').text(kolicina);
        if (kolicina > 0) {
            $('.kosara-broj').addClass('vidljiv');
        } else {
            $('.kosara-broj').removeClass('vidljiv');
        }

        renderStavke(kosara);
        $('.kosara-subtotal-iznos').text(formatirajCijenu(cijena));
        $('.kosara-ukupno-iznos').text(formatirajCijenu(cijena));
    }

    /* Otvaranje i zatvaranje kosarice */
    $('.kosara-gumb').click(function () {
        $('.kosara-panel').addClass('otvoren');
        $('.kosara-overlay').fadeIn(300);
        $('body').css('overflow', 'hidden');
    });

    $('.kosara-zatvori, .kosara-overlay').click(function () {
        $('.kosara-panel').removeClass('otvoren');
        $('.kosara-overlay').fadeOut(300);
        $('body').css('overflow', '');
    });

    /* Gumb "+" na karticama */
    $('.gumb-dodaj').click(function () {
        var $g = $(this);
        dodajProizvod(
            $g.data('id'),
            $g.data('naziv'),
            $g.data('opis'),
            $g.data('cijena'),
            $g.data('slika')
        );

        $g.css({ 'transform': 'scale(1.3)', 'background-color': '#495440', 'color': '#FFFFFF', 'border-color': '#495440' });
        setTimeout(function () {
            $g.css({ 'transform': '', 'background-color': '', 'color': '', 'border-color': '' });
        }, 250);
    });

    /* Pokrecem kosaricu */
    osvjeziKosaru();

});