<?php
// Dummy data - replace with your DB query
$doctors = [
    ['id'=>1,'initials'=>'AJ','name'=>'Dr. Adam Johnson','specialty'=>'Cardiologist','email'=>'adam@bci.com','phone'=>'+94 77 123 4567','status'=>'Available'],
    ['id'=>2,'initials'=>'SM','name'=>'Dr. Sarah Miller','specialty'=>'Neurologist','email'=>'sarah@bci.com','phone'=>'+94 77 234 5678','status'=>'Busy'],
    ['id'=>3,'initials'=>'RK','name'=>'Dr. Raj Kumar','specialty'=>'Orthopedic','email'=>'raj@bci.com','phone'=>'+94 77 345 6789','status'=>'Available'],
    ['id'=>4,'initials'=>'EW','name'=>'Dr. Emily Watson','specialty'=>'Pediatrician','email'=>'emily@bci.com','phone'=>'+94 77 456 7890','status'=>'Available'],
    ['id'=>5,'initials'=>'TL','name'=>'Dr. Tom Lee','specialty'=>'Dermatologist','email'=>'tom@bci.com','phone'=>'+94 77 567 8901','status'=>'Busy'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Doctors - BCI Healthcare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        /* ── Header ── */
        .page-header {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: #fff;
            padding: 20px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }
        .page-header .logo {
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header .back-link {
            color: #fff;
            text-decoration: none;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.15);
            padding: 7px 14px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .page-header .back-link:hover { background: rgba(255,255,255,0.25); }

        /* ── Main container ── */
        .container {
            max-width: 1100px;
            margin: 32px auto;
            padding: 0 20px;
        }

        /* ── Top bar ── */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .top-bar h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .top-bar h2 i { color: #3b82f6; }

        .total-badge {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #bfdbfe;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        /* ── Search bar ── */
        .search-wrap {
            position: relative;
            width: 280px;
        }
        .search-wrap input {
            width: 100%;
            padding: 9px 14px 9px 38px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            font-size: 0.88rem;
            outline: none;
            background: #fff;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .search-wrap input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* ── Doctor cards grid ── */
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 18px;
        }

        .doctor-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.07);
            border: 1px solid #f1f5f9;
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .doctor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(59,130,246,0.12);
            border-color: #bfdbfe;
        }

        .card-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .doc-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(99,102,241,0.3);
        }

        .doc-info { flex: 1; }
        .doc-name {
            font-size: 0.98rem;
            font-weight: 700;
            color: #1e293b;
        }
        .doc-specialty {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .doc-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .doc-status.available {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .doc-status.busy {
            background: #fff1f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .card-divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 0;
        }

        .card-details {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .detail-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.83rem;
            color: #475569;
        }
        .detail-row i {
            width: 16px;
            color: #3b82f6;
            font-size: 0.8rem;
        }
        .detail-row a {
            color: #3b82f6;
            text-decoration: none;
        }
        .detail-row a:hover { text-decoration: underline; }

        /* ── No results ── */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
            display: none;
        }
        .no-results i { font-size: 2.5rem; margin-bottom: 12px; display: block; }
        .no-results p { font-size: 0.95rem; }

        /* ── Footer ── */
        .page-footer {
            text-align: center;
            padding: 24px;
            color: #94a3b8;
            font-size: 0.82rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="logo">
            <i class="fa-solid fa-hospital"></i> BCI HealthCare Center
        </div>
        <a href="javascript:window.close()" class="back-link">
            <i class="fa-solid fa-xmark"></i> Close Tab
        </a>
    </div>

    <div class="container">
        <div class="top-bar">
            <h2>
                <i class="fa-solid fa-stethoscope"></i> All Doctors
                <span class="total-badge"><?= count($doctors) ?> Total</span>
            </h2>
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search by name or specialty..." oninput="filterDoctors()">
            </div>
        </div>

        <div class="doctors-grid" id="doctorsGrid">
            <?php foreach ($doctors as $doc):
                $statusClass = strtolower($doc['status']) === 'available' ? 'available' : 'busy';
            ?>
            <div class="doctor-card" data-name="<?= strtolower($doc['name']) ?>" data-specialty="<?= strtolower($doc['specialty']) ?>">
                <div class="card-top">
                    <div class="doc-avatar"><?= htmlspecialchars($doc['initials']) ?></div>
                    <div class="doc-info">
                        <div class="doc-name"><?= htmlspecialchars($doc['name']) ?></div>
                        <div class="doc-specialty"><?= htmlspecialchars($doc['specialty']) ?></div>
                    </div>
                    <span class="doc-status <?= $statusClass ?>">
                        <i class="fa-solid fa-circle" style="font-size:0.5rem"></i>
                        <?= htmlspecialchars($doc['status']) ?>
                    </span>
                </div>
                <hr class="card-divider">
                <div class="card-details">
                    <div class="detail-row">
                        <i class="fa-solid fa-envelope"></i>
                        <a href="mailto:<?= htmlspecialchars($doc['email']) ?>"><?= htmlspecialchars($doc['email']) ?></a>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-phone"></i>
                        <span><?= htmlspecialchars($doc['phone']) ?></span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>Doctor ID: BCI-<?= str_pad($doc['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="no-results" id="noResults">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>No doctors found matching your search.</p>
        </div>

        <div class="page-footer">
            &copy; 2026 BCI Healthcare Center. All rights reserved.
        </div>
    </div>

    <script>
        function filterDoctors() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.doctor-card');
            let visible = 0;

            cards.forEach(card => {
                const name     = card.dataset.name;
                const specialty = card.dataset.specialty;
                const match = name.includes(query) || specialty.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
        }
    </script>
</body>
</html>