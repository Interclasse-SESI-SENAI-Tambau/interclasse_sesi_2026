<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Interclasse</title>

    <style>

        /* ==============================
           FOOTER
           ============================== */

        .footer-interclasse {
            position: relative;
            overflow: hidden;
            background: linear-gradient(
                135deg,
                #07111f,
                #0b1f38,
                #123d63
            );

            color: white;
            padding: 50px 8% 20px;
        }


        /* BRILHOS DO FUNDO */

        .footer-interclasse::before {
            content: "";
            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            background: rgba(30, 107, 231, 0.18);

            top: -150px;
            left: -100px;

            filter: blur(50px);

            animation: brilho 5s infinite alternate;
        }


        .footer-interclasse::after {
            content: "";
            position: absolute;

            width: 250px;
            height: 250px;

            border-radius: 50%;

            background: rgba(71, 205, 253, 0.12);

            right: -80px;
            bottom: -150px;

            filter: blur(50px);

            animation: brilho 4s infinite alternate-reverse;
        }


        /* CONTEÚDO */

        .footer-conteudo {
            position: relative;
            z-index: 2;

            display: grid;

            grid-template-columns:
                2fr 1fr 1fr;

            gap: 50px;
        }


        /* LOGO */

        .footer-logo h2 {
            color: #47cdfd;

            font-size: 30px;

            margin-bottom: 10px;

            letter-spacing: 2px;
        }


        .footer-logo p {
            color: #cbd5e1;
        }


        /* TÍTULOS */

        .footer-links h3,
        .footer-info h3 {
            color: #47cdfd;

            margin-bottom: 15px;
        }


        /* LINKS */

        .footer-links {
            display: flex;

            flex-direction: column;

            gap: 8px;
        }


        .footer-links a {
            width: fit-content;

            color: #e2e8f0;

            text-decoration: none;

            transition: 0.3s;
        }


        .footer-links a:hover {
            color: #47cdfd;

            transform: translateX(8px);
        }


        /* INFORMAÇÕES */

        .footer-info p {
            color: #cbd5e1;

            margin: 8px 0;

            transition: 0.3s;
        }


        .footer-info p:hover {
            color: white;

            transform: translateX(5px);
        }


        /* LINHA */

        .linha-footer {
            position: relative;

            z-index: 2;

            height: 1px;

            margin: 35px 0 20px;

            background: linear-gradient(
                90deg,
                transparent,
                #47cdfd,
                transparent
            );
        }


        /* FINAL */

        .footer-final {
            position: relative;

            z-index: 2;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .footer-final p {
            color: #94a3b8;

            font-size: 14px;
        }


        /* ÍCONES */

        .bolas {
            display: flex;

            gap: 12px;
        }


        .bolas span {
            display: flex;

            justify-content: center;
            align-items: center;

            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.08);

            transition: 0.3s;

            animation: flutuar 3s ease-in-out infinite;
        }


        .bolas span:nth-child(2) {
            animation-delay: 0.3s;
        }


        .bolas span:nth-child(3) {
            animation-delay: 0.6s;
        }


        .bolas span:hover {
            transform: scale(1.2);

            background: rgba(71, 205, 253, 0.2);
        }


        /* ANIMAÇÃO DOS ÍCONES */

        @keyframes flutuar {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }

        }


        /* ANIMAÇÃO DOS BRILHOS */

        @keyframes brilho {

            from {
                transform: scale(1);

                opacity: 0.5;
            }

            to {
                transform: scale(1.3);

                opacity: 0.9;
            }

        }


        /* CELULAR */

        @media (max-width: 768px) {

            .footer-conteudo {
                grid-template-columns: 1fr;

                gap: 30px;
            }


            .footer-final {
                flex-direction: column;

                gap: 15px;

                text-align: center;
            }

        }

    </style>

</head>


<body>


    <!-- FOOTER -->

    <footer class="footer-interclasse">

        <div class="footer-conteudo">


            <!-- LOGO -->

            <div class="footer-logo">

                <h2>INTERCLASSE</h2>

                <p>
                    Competição, união e espírito esportivo.
                </p>

                <img src=./assets/icons/sesi_cup.png width="200" height="200">

            </div>


            <!-- MENU -->

            <div class="footer-links">

                <h3>Menu</h3>

                <a href="#">Início</a>

                <a href="#">Jogos</a>

                <a href="#">Times</a>

                <a href="#">Chaves</a>

                <a href="#">Resultados</a>

                <a href="#">                                          Área Administrativa</a>

            </div>


            <!-- INFORMAÇÕES -->

            <div class="footer-info">

                    <p>🏆 <br> Torça pelo seu time</p>

                    <p>⚡ <br> Viva a competição</p>

                    <p>🤝 <br> Respeite seus adversários</p>

            </div>


        </div>


        <!-- LINHA -->

        <div class="linha-footer"></div>


        <!-- RODAPÉ -->

        <div class="footer-final">

            <p>
                © 2026 Interclasse — Todos os direitos reservados.
            </p>


            <div class="bolas">

                <span>⚽</span>

                <span>🏀</span>

                <span>🏐</span>

            </div>

        </div>


    </footer>


</body>

</html>