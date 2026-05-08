<?php
// Dummy data - replace with your DB query
$patients = [
    ['id'=>1,'initials'=>'JD','name'=>'John Doe','age'=>45,'gender'=>'Male','condition'=>'Heart Disease','condition_type'=>'red','doctor'=>'Dr. Adam Johnson','phone'=>'+94 77 111 2233','email'=>'john@email.com','status'=>'Stable'],
    ['id'=>2,'initials'=>'MA','name'=>'Maria Ahmed','age'=>32,'gender'=>'Female','condition'=>'Migraine','condition_type'=>'red','doctor'=>'Dr. Sarah Miller','phone'=>'+94 77 222 3344','email'=>'maria@email.com','status'=>'Stable'],
    ['id'=>3,'initials'=>'SP','name'=>'Sam Perera','age'=>58,'gender'=>'Male','condition'=>'Fracture','condition_type'=>'red','doctor'=>'Dr. Raj Kumar','phone'=>'+94 77 333 4455','email'=>'sam@email.com','status'=>'Critical'],
    ['id'=>4,'initials'=>'LB','name'=>'Lily Brown','age'=>7,'gender'=>'Female','condition'=>'Flu','condition_type'=>'green','doctor'=>'Dr. Emily Watson','phone'=>'+94 77 444 5566','email'=>'lily@email.com','status'=>'Recovered'],
    ['id'=>5,'initials'=>'KN','name'=>'Kevin Nair','age'=>29,'gender'=>'Male','condition'=>'Eczema','condition_type'=>'green','doctor'=>'Dr. Tom Lee','phone'=>'+94 77 555 6677','email'=>'kevin@email.com','status'=>'Stable'],
    ['id'=>6,'initials'=>'RP','name'=>'Rita Patel','age'=>51,'gender'=>'Female','condition'=>'Diabetes','condition_type'=>'red','doctor'=>'Dr. Adam Johnson','phone'=>'+94 77 666 7788','email'=>'rita@email.com','status'=>'Stable'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Patients - BCI Healthcare</title>
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
        .page-header .close-btn {
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
            cursor: pointer;
            border: none;
        }
        .page-header .close-btn:hover { background: rgba(255,255,255,0.25); }

        /* ── Container ── */
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
        .top-bar h2 i { color: #10b981; }

        .total-badge {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        /* ── Search ── */
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
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        .search-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* ── Patient cards grid ── */
        .patients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 18px;
        }

        .patient-card {
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
        .patient-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(16,185,129,0.12);
            border-color: #bbf7d0;
        }

        .card-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pat-avatar {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(16,185,129,0.3);
        }

        .pat-info { flex: 1; }
        .pat-name {
            font-size: 0.98rem;
            font-weight: 700;
            color: #1e293b;
        }
        .pat-meta {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 2px;
        }

        /* Status badge */
        .pat-status {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .pat-status.stable    { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .pat-status.critical  { background: #fff1f2; color: #dc2626; border: 1px solid #fecaca; }
        .pat-status.recovered { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

        .card-divider { border: none; border-top: 1px solid #f1f5f9; margin: 0; }

        /* Condition badge */
        .condition-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
        }
        .condition-red   { background: #fff1f2; color: #dc2626; border: 1px solid #fecaca; }
        .condition-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

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
            color: #10b981;
            font-size: 0.8rem;
        }
        .detail-row a { color: #3b82f6; text-decoration: none; }
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
        <button class="close-btn" onclick="window.close()">
            <i class="fa-solid fa-xmark"></i> Close Tab
        </button>
    </div>

    <div class="container">
        <div class="top-bar">
            <h2>
                <i class="fa-solid fa-user-injured"></i> All Patients
                <span class="total-badge"><?= count($patients) ?> Total</span>
            </h2>
            <div class="search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Search by name or condition..." oninput="filterPatients()">
            </div>
        </div>

        <div class="patients-grid" id="patientsGrid">
            <?php foreach ($patients as $p):
                $statusClass = match(strtolower($p['status'])) {
                    'stable'    => 'stable',
                    'critical'  => 'critical',
                    'recovered' => 'recovered',
                    default     => 'stable'
                };
                $condClass = 'condition-' . $p['condition_type'];
            ?>
            <div class="patient-card"
                 data-name="<?= strtolower($p['name']) ?>"
                 data-condition="<?= strtolower($p['condition']) ?>">

                <div class="card-top">
                    <div class="pat-avatar"><?= htmlspecialchars($p['initials']) ?></div>
                    <div class="pat-info">
                        <div class="pat-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="pat-meta">
                            Age <?= $p['age'] ?> &bull; <?= htmlspecialchars($p['gender']) ?>
                        </div>
                    </div>
                    <span class="pat-status <?= $statusClass ?>">
                        <i class="fa-solid fa-circle" style="font-size:0.5rem"></i>
                        <?= htmlspecialchars($p['status']) ?>
                    </span>
                </div>

                <hr class="card-divider">

                <div class="card-details">
                    <div class="detail-row">
                        <i class="fa-solid fa-stethoscope"></i>
                        <span>
                            Condition:
                            <span class="condition-badge <?= $condClass ?>">
                                <?= htmlspecialchars($p['condition']) ?>
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-user-doctor"></i>
                        <span><?= htmlspecialchars($p['doctor']) ?></span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-phone"></i>
                        <span><?= htmlspecialchars($p['phone']) ?></span>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-envelope"></i>
                        <a href="mailto:<?= htmlspecialchars($p['email']) ?>"><?= htmlspecialchars($p['email']) ?></a>
                    </div>
                    <div class="detail-row">
                        <i class="fa-solid fa-id-card"></i>
                        <span>Patient ID: PAT-<?= str_pad($p['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="no-results" id="noResults">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>No patients found matching your search.</p>
        </div>

        <div class="page-footer">
            &copy; 2026 BCI Healthcare Center. All rights reserved.
        </div>
    </div>

    <script>
        function filterPatients() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.patient-card');
            let visible = 0;

            cards.forEach(card => {
                const match = card.dataset.name.includes(query) || card.dataset.condition.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
        }
    </script>
</body>
</html>