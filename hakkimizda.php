<?php
$page_title = "Hakkımızda - Sıcak Patizi";
include("includes/header.php");
?>

<div class="about-page-shell">
    <main class="about-page container mx-auto px-4 py-10 mt-10">
        <section class="about-hero">
            <h1>Sıcak <span>Patizi</span></h1>
            <p>
                Biz, can dostlarin satin alinacak bir urun degil; sevgiyle yasatilacak bir aile uyesi olduguna inanan bir topluluguz.
                Platformumuz, sahiplendirme surecini herkes icin daha seffaf, guvenli ve kolay hale getirmek icin tasarlandi.
            </p>
        </section>

        <section class="about-grid">
            <article class="about-card">
                <h2>Misyonumuz</h2>
                <p>
                    Barinaklarda ve sokakta yasayan hayvanlarin kalici, sevgi dolu yuvalara ulasmasini hizlandirmak.
                    Bunu yaparken hayvan refahini onceleyen bir dijital deneyim sunmak.
                </p>
            </article>

            <article class="about-card">
                <h2>Vizyonumuz</h2>
                <p>
                    Turkiye'de sahiplendirme kulturunu guclendiren, guvenilir ve modern bir referans platform olmak.
                    Toplumsal farkindaligi artirarak satin alma yerine sahiplenmeyi norm haline getirmek.
                </p>
            </article>

            <article class="about-card">
                <h2>Degerlerimiz</h2>
                <ul>
                    <li>Seffaflik ve guven</li>
                    <li>Hayvan refahina saygi</li>
                    <li>Sorumlu sahiplenme bilinci</li>
                    <li>Toplumsal dayanismanin gucu</li>
                </ul>
            </article>
        </section>

        <section class="about-cta-box">
            <h3>Bir canin hayati degisebilir</h3>
            <p>
                Dogru eslesme, dogru bilgilendirme ve sorumlu kararlarla her sahiplenme bir hayat hikayesini guzellestirebilir.
                Sen de bir adim at, bir cana yuva ol.
            </p>
            <a href="ilanlar.php" class="about-cta-button">Ilanlari Incele</a>
        </section>
    </main>

    <style>
        :root {
            --bg-light: #F8F9FA;
            --primary-pink: #FFB3C6;
            --action-mint: #A8DADC;
            --text-dark: #2B2D42;
        }

        .about-page-shell {
            background:
                radial-gradient(circle at 12% 15%, rgba(255, 179, 198, 0.24), transparent 42%),
                radial-gradient(circle at 88% 10%, rgba(168, 218, 220, 0.26), transparent 40%),
                var(--bg-light);
            min-height: calc(100vh - 64px);
        }

        .about-page {
            color: var(--text-dark);
        }

        .about-hero,
        .about-card,
        .about-cta-box {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--primary-pink);
            border-radius: 16px;
            box-shadow: 0 14px 34px rgba(43, 45, 66, 0.1);
        }

        .about-hero {
            text-align: center;
            padding: 2.25rem;
            margin-bottom: 1.5rem;
        }

        .about-hero h1 {
            font-size: clamp(1.9rem, 3vw, 2.6rem);
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.85rem;
        }

        .about-hero h1 span {
            color: #3A868F;
        }

        .about-hero p {
            font-size: 1.03rem;
            line-height: 1.75;
            color: var(--text-dark);
            max-width: 840px;
            margin: 0 auto;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .about-card {
            padding: 1.5rem;
        }

        .about-card h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            text-align: left;
        }

        .about-card p,
        .about-card li {
            color: var(--text-dark);
            line-height: 1.7;
        }

        .about-card ul {
            margin: 0;
            padding-left: 1.1rem;
            display: grid;
            gap: 0.4rem;
        }

        .about-cta-box {
            padding: 2rem;
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .about-cta-box h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .about-cta-box p {
            color: var(--text-dark);
            line-height: 1.7;
            max-width: 760px;
            margin: 0 auto 1.2rem;
        }

        .about-cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.4rem;
            border-radius: 14px;
            background: var(--action-mint);
            color: var(--text-dark);
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 10px 24px rgba(43, 45, 66, 0.14);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .about-cta-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(43, 45, 66, 0.16);
            color: var(--text-dark);
        }

        @media (max-width: 992px) {
            .about-grid {
                grid-template-columns: 1fr;
            }

            .about-hero,
            .about-card,
            .about-cta-box {
                border-radius: 14px;
            }
        }
    </style>
<?php include("includes/footer.php"); ?>
