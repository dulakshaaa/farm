<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Navigation Bar</title>
    <style>
        :root {
            /* Color System */
            --primary-500: #4f46e5;
            --primary-400: #6366f1;
            --primary-600: #4338ca;
            
            --dark-100: #0f172a;
            --dark-200: #1e293b;
            --dark-300: #334155;
            
            --light-100: #f8fafc;
            --light-200: #e2e8f0;
            --light-300: #94a3b8;
            
            /* Effects */
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Spacing */
            --radius-sm: 0.25rem;
            --radius-md: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--dark-100);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .navbar {
            background-color: var(--dark-200);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            padding: 0.75rem 1.5rem;
            margin: 1rem auto;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 0.5rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: var(--light-200);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link:hover {
            background-color: var(--dark-300);
            color: var(--primary-400);
        }

        .nav-link.active {
            background-color: var(--primary-500);
            color: white;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.3);
            position: relative;
        }

        .nav-link.active:hover {
            background-color: var(--primary-600);
        }

        /* Active item indicator */
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background-color: var(--primary-400);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        .nav-link i {
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .nav-link.active i {
            transform: scale(1.1);
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .navbar {
            animation: fadeIn 0.4s ease-out forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0 1rem;
            }
            
            .navbar {
                padding: 0.5rem;
                border-radius: var(--radius-sm);
            }
            
            .nav-menu {
                gap: 0.25rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            .nav-link.active::after {
                bottom: -4px;
                width: 4px;
                height: 4px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="navbar-container">
        <nav class="navbar">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../c-home.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'c-home.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../add/c-farmeradd.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'c-farmeradd.php') ? 'active' : ''; ?>">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add</span>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
</body>
</html>