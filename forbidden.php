<!DOCTYPE html>
<html>
<head>
    <title>Acceso Denegado</title>
    <style>
        body {
            background-color: #f7f7f7;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }

        .container {
            text-align: center;
        }

        .lock-icon {
            font-size: 120px;
            color: #e74c3c;
        }

        .error-text {
            font-size: 36px;
            color: #333;
            margin-top: 30px;
        }

        @keyframes swing {
            0% {
                transform: rotate(0deg);
            }
            10% {
                transform: rotate(10deg);
            }
            30% {
                transform: rotate(-10deg);
            }
            50% {
                transform: rotate(5deg);
            }
            70% {
                transform: rotate(-5deg);
            }
            90% {
                transform: rotate(2deg);
            }
            100% {
                transform: rotate(0deg);
            }
        }

        .lock-icon {
            animation: swing 1s infinite;
            transform-origin: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="lock-icon">&#x1F512;</div>
        <h1 class="error-text">Acceso Denegado</h1>
    </div>
</body>
</html>

