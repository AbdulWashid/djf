<x-frontend.main>

    <style>
        .custom-404 {
            background: radial-gradient(circle at top left, #f8f9fa, #ffffff);
            position: relative;
            overflow: hidden;
        }

        .animate-bounce {
            display: inline-block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-25px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        /* Optional floating shapes for creativity */
        .custom-404::before, .custom-404::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.1);
            animation: float 8s ease-in-out infinite;
        }

        .custom-404::before {
            width: 150px;
            height: 150px;
            top: 10%;
            left: 5%;
        }

        .custom-404::after {
            width: 200px;
            height: 200px;
            bottom: 10%;
            right: 10%;
            animation-delay: 2s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(20deg);
            }
        }


    </style>

    <main class="d-flex flex-column justify-content-center align-items-center min-vh-100 text-center bg-light custom-404">
        <div class="container">
            <h1 class="display-1 fw-bold   mb-3 animate-bounce" style="color: #444690">404</h1>
            <h2 class="fw-semibold text-dark mb-4">Oops! Page Not Found</h2>
            <p class="lead text-muted mb-5">
                The page you’re looking for might have been moved, deleted, or never existed.
            </p>
            <a href="/" class="btn btn-default mt-3 btn-lg px-4">
                Back to Home
            </a>
        </div>
    </main>


</x-frontend.main>
