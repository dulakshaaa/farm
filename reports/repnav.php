<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Files | Farm Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Using your CSS variables */
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #10b981;
            --secondary-dark: #0d9488;
            --accent: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --dark-1: #0f172a;
            --dark-2: #1e293b;
            --dark-3: #334155;
            --light-1: #f8fafc;
            --light-2: #e2e8f0;
            --light-3: #94a3b8;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 0.25rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-1);
            color: var(--light-1);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .master-files-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--light-1);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .page-title::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .page-subtitle {
            color: var(--light-3);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .nav-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .nav-card {
            background: var(--dark-2);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
        }

        .nav-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(79, 70, 229, 0.8);
        }

        .nav-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            
            opacity: 0;
            transition: var(--transition);
        }

        .nav-card:hover::before {
            opacity: 1;
        }

        .card-icon {
            padding: 2rem;
            text-align: center;
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-light);
            font-size: 2.5rem;
        }

        .card-body {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .card-title {
            font-weight: 600;
            color: var(--light-1);
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
        }

        .card-text {
            color: var(--light-3);
            font-size: 0.9375rem;
            margin-bottom: 1.5rem;
        }

        .card-footer {
            padding: 0 1.5rem 1.5rem;
        }

        .btn-card {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background-color: var(--primary);
            color: white;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-card:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-card i {
            font-size: 0.75rem;
        }

        @media (max-width: 768px) {
            .master-files-container {
                padding: 1.5rem;
            }
            
            .nav-cards {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 1.75rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-card {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .nav-card:nth-child(1) { animation-delay: 0.1s; }
        .nav-card:nth-child(2) { animation-delay: 0.2s; }
        .nav-card:nth-child(3) { animation-delay: 0.3s; }
        .nav-card:nth-child(4) { animation-delay: 0.4s; }
        .nav-card:nth-child(5) { animation-delay: 0.5s; }
        .nav-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="master-files-container">
        <div class="page-header">
            <h1 class="page-title">Master Files</h1>
            <p class="page-subtitle">Manage all system configurations and foundational data elements</p>
        </div>
        
        <div class="nav-cards">
            <a href="areamast.php" class="nav-card">
                <div class="card-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Area Management</h3>
                    <p class="card-text">Define and manage geographical areas, zones, and regional configurations for farm operations.</p>
                </div>
                <div class="card-footer">
                    <button class="btn-card">
                        <span>Manage Areas</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
            
            <a href="breedmast.php" class="nav-card">
                <div class="card-icon">
                    <i class="fas fa-dna"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Breed Management</h3>
                    <p class="card-text">Configure animal breeds, genetic profiles, and breeding program specifications.</p>
                </div>
                <div class="card-footer">
                    <button class="btn-card">
                        <span>Manage Breeds</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
            
            <a href="farma.php" class="nav-card">
                <div class="card-icon">
                    <i class="fas fa-tractor"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Farm Management</h3>
                    <p class="card-text">Register and maintain comprehensive farm details, locations, and operational parameters.</p>
                </div>
                <div class="card-footer">
                    <button class="btn-card">
                        <span>Manage Farms</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
            
            <a href="flomast.php" class="nav-card">
                <div class="card-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Field Officers</h3>
                    <p class="card-text">Configure field officers, their assignments, and operational territories.</p>
                </div>
                <div class="card-footer">
                    <button class="btn-card">
                        <span>Manage Officers</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
            
            <a href="workermast.php" class="nav-card">
                <div class="card-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title">Worker Management</h3>
                    <p class="card-text">Maintain comprehensive worker records, roles, and employment details.</p>
                </div>
                <div class="card-footer">
                    <button class="btn-card">
                        <span>Manage Workers</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
            
            <a href="system_settings.php" class="nav-card">
                <div class="card-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div class="card-body">
                    <h3 class="card-title">System Configuration</h3>
                    <p class="card-text">Manage global system parameters, defaults, and application settings.</p>
                </div>
                <div class="card-footer">
                    <button class="btn-card">
                        <span>Configure System</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>