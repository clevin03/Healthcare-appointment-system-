<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>HealthCare — Doctor Portal</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',sans-serif;background:#f4f6fb;min-height:100vh;}

/* ── LOGIN / REGISTER PAGE ───────────────────────────────────────────── */
.login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#6C4FC4 0%,#9B7FE8 100%);padding:24px 16px;}
.login-card{background:#fff;border-radius:16px;padding:36px 36px 32px;width:100%;max-width:440px;box-shadow:0 24px 70px rgba(44,47,63,0.2);}
.login-logo{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:10px;}
.login-logo i{font-size:36px;color:#6C4FC4;}
.login-logo span{font-size:24px;font-weight:700;color:#2C2F3F;}
.login-title{font-size:18px;font-weight:700;color:#2C2F3F;text-align:center;margin-bottom:4px;}
.login-sub{font-size:13px;color:#9ca3c0;text-align:center;margin-bottom:20px;}

/* ── TABS ────────────────────────────────────────────────────────────── */
.auth-tabs{display:flex;border-bottom:2px solid #ede9fb;margin-bottom:22px;}
.auth-tab{flex:1;background:none;border:none;border-bottom:2.5px solid transparent;margin-bottom:-2px;padding:10px 0;font-size:13px;font-weight:600;font-family:'Segoe UI',sans-serif;color:#9ca3c0;cursor:pointer;transition:color .15s,border-color .15s;}
.auth-tab.active{color:#6C4FC4;border-bottom-color:#6C4FC4;}
.auth-tab:hover:not(.active){color:#534AB7;}
.auth-panel{display:none;}
.auth-panel.active{display:block;}

/* ── ALERT ───────────────────────────────────────────────────────────── */
.alert-box{border-radius:8px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;}
.alert-box i{flex-shrink:0;font-size:15px;margin-top:1px;}
.alert-success{background:#f0fff4;color:#276749;border:1px solid #c6f6d5;}
.alert-danger{background:#fff5f5;color:#c53030;border:1px solid #fed7d7;}

/* ── FIELD ───────────────────────────────────────────────────────────── */
.field{margin-bottom:16px;}
.field label{display:block;font-size:12px;font-weight:600;color:#534AB7;margin-bottom:7px;letter-spacing:0.3px;}
.field label .req{color:#6C4FC4;margin-left:2px;}
.field-wrap{position:relative;}
.field-wrap .fi{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:17px;color:#9ca3c0;pointer-events:none;}
.field input,.field select{width:100%;padding:11px 14px 11px 40px;border:1.5px solid #e0daf5;border-radius:9px;font-size:13px;color:#2C2F3F;background:#f8f7ff;outline:none;transition:border-color .15s,box-shadow .15s;appearance:none;font-family:'Segoe UI',sans-serif;}
.field select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3c0' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;background-color:#f8f7ff;padding-right:36px;}
.field input:focus,.field select:focus{border-color:#6C4FC4;box-shadow:0 0 0 3px rgba(108,79,196,0.1);background:#fff;}
.field input.err,.field select.err{border-color:#EF4444;background:#fff5f5;}
.eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3c0;font-size:17px;display:flex;align-items:center;padding:2px;}
.eye-btn:hover{color:#6C4FC4;}
.err-msg{font-size:11px;color:#EF4444;margin-top:5px;display:flex;align-items:center;gap:4px;}

/* ── FIELD ROW ───────────────────────────────────────────────────────── */
.field-row{display:flex;gap:12px;}
.field-row .field{flex:1;}

/* ── SECTION LABEL ───────────────────────────────────────────────────── */
.sec-lbl{font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#9ca3c0;margin:4px 0 12px;}
.sec-divider{border:none;border-top:1px solid #ede9fb;margin:4px 0 16px;}

/* ── BUTTONS ─────────────────────────────────────────────────────────── */
.login-btn{width:100%;background:#6C4FC4;color:#fff;border:none;padding:13px;border-radius:9px;font-size:15px;font-weight:700;cursor:pointer;margin-top:4px;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:8px;font-family:'Segoe UI',sans-serif;}
.login-btn:hover{background:#534AB7;}

/* ── DEMO ACCOUNTS ───────────────────────────────────────────────────── */
.divider{display:flex;align-items:center;gap:10px;margin:20px 0 16px;}
.divider span{font-size:12px;color:#c5bff0;white-space:nowrap;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#ede9fb;}
.demo-accounts{background:#f4f0ff;border-radius:10px;padding:14px 16px;}
.demo-accounts p{font-size:12px;font-weight:700;color:#534AB7;margin-bottom:10px;display:flex;align-items:center;gap:6px;}
.demo-item{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #ede9fb;}
.demo-item:last-child{border-bottom:none;}
.demo-info{display:flex;align-items:center;gap:10px;}
.demo-avatar{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;}
.demo-name{font-size:12px;font-weight:600;color:#2C2F3F;}
.demo-spec{font-size:11px;color:#9ca3c0;}
.demo-creds{font-size:11px;color:#9ca3c0;text-align:right;}
.demo-creds strong{color:#534AB7;display:block;}
.use-btn{background:#EEEDFE;border:none;color:#534AB7;font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;cursor:pointer;margin-left:8px;transition:background .15s;}
.use-btn:hover{background:#6C4FC4;color:#fff;}

/* ── APP SHELL ───────────────────────────────────────────────────────── */
.app{display:flex;flex-direction:column;min-height:100vh;}
.topbar{background:#6C4FC4;display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:60px;flex-shrink:0;}
.topbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-size:18px;font-weight:700;}
.topbar-brand i{font-size:24px;}
.topbar-right{display:flex;align-items:center;gap:14px;color:#fff;font-size:14px;}
.topbar-avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,0.4);}
.logout-btn{background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:7px 16px;border-radius:7px;cursor:pointer;font-size:13px;transition:background .15s;display:flex;align-items:center;gap:6px;}
.logout-btn:hover{background:rgba(255,255,255,0.28);}
.body{display:flex;flex:1;}
.sidebar{width:200px;background:#2C2F3F;flex-shrink:0;padding:18px 0;}
.nav-item{display:flex;align-items:center;gap:10px;padding:11px 20px;color:#9ca3c0;font-size:13px;cursor:pointer;border-left:3px solid transparent;transition:all .15s;}
.nav-item:hover{background:rgba(255,255,255,0.06);color:#fff;}
.nav-item.active{color:#fff;border-left:3px solid #7c6ee0;background:rgba(255,255,255,0.08);}
.nav-item i{font-size:18px;}
.content{flex:1;padding:24px;overflow:auto;}
.section-title{font-size:16px;font-weight:700;color:#2C2F3F;margin-bottom:16px;}

/* ── DASHBOARD ───────────────────────────────────────────────────────── */
.welcome-box{background:#fff;border:1px solid #e8e4f8;border-radius:12px;padding:20px 24px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between;gap:20px;}
.welcome-box-left p{font-size:14px;color:#6b7280;line-height:1.8;margin-bottom:14px;}
.welcome-box-left h3{font-size:17px;font-weight:700;color:#2C2F3F;margin-bottom:6px;}
.welcome-doc-badge{display:flex;align-items:center;gap:12px;background:#f4f0ff;border-radius:10px;padding:12px 16px;}
.welcome-doc-badge .ava{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0;}
.welcome-doc-badge .info strong{font-size:14px;color:#2C2F3F;display:block;}
.welcome-doc-badge .info small{font-size:12px;color:#9ca3c0;}
.view-btn{background:#6C4FC4;color:#fff;border:none;padding:10px 20px;border-radius:7px;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:background .15s;}
.view-btn:hover{background:#534AB7;}
.status-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
.stat-card{background:#fff;border:1px solid #e8e4f8;border-radius:12px;padding:18px 14px;text-align:center;transition:border-color .15s;cursor:pointer;}
.stat-card:hover{border-color:#7c6ee0;}
.stat-card.hl{border:2px solid #7c6ee0;}
.stat-icon{font-size:24px;color:#c5bff0;margin-bottom:8px;}
.stat-num{font-size:30px;font-weight:700;color:#534AB7;margin-bottom:4px;}
.stat-label{font-size:12px;color:#9ca3c0;}
.appt-box{background:#fff;border:1px solid #e8e4f8;border-radius:12px;overflow:hidden;margin-top:0;}
.appt-hdr{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f0edf8;}
.appt-hdr span{font-size:14px;font-weight:600;color:#2C2F3F;display:flex;align-items:center;gap:8px;}
.hide-btn{display:flex;align-items:center;gap:5px;font-size:12px;color:#534AB7;cursor:pointer;background:none;border:none;transition:opacity .15s;}
.hide-btn:hover{opacity:0.7;}
.appt-bdy{padding:18px;}
.search-row{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.search-row input{flex:1;background:#f4f6fb;border:1px solid #e0daf5;border-radius:8px;padding:9px 14px;font-size:13px;color:#2C2F3F;outline:none;transition:border-color .15s;}
.search-row input:focus{border-color:#7c6ee0;}
.city-pills{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;}
.pill{padding:5px 16px;border-radius:20px;font-size:12px;border:1px solid #7c6ee0;background:#fff;color:#534AB7;cursor:pointer;transition:all .15s;}
.pill:hover{background:#EEEDFE;}
.pill.active{background:#6C4FC4;color:#fff;border-color:#6C4FC4;}
.count-lbl{font-size:12px;color:#9ca3c0;margin-bottom:12px;}
.hosp-card{background:#f9f8fe;border:1px solid #ede9fb;border-radius:10px;padding:13px 16px;display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;transition:border-color .15s;}
.hosp-card:hover{border-color:#7c6ee0;}
.hc-left{display:flex;align-items:center;gap:12px;}
.hc-ico{width:40px;height:40px;border-radius:50%;background:#EEEDFE;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.hc-ico i{font-size:18px;color:#534AB7;}
.hc-name{font-size:14px;font-weight:600;color:#2C2F3F;margin-bottom:3px;}
.hc-meta{font-size:12px;color:#9ca3c0;}
.city-badge{display:inline-block;font-size:11px;padding:2px 9px;border-radius:20px;background:#EEEDFE;color:#534AB7;margin-left:8px;font-weight:500;}
.book-btn{background:#6C4FC4;color:#fff;border:none;padding:8px 16px;border-radius:7px;font-size:12px;cursor:pointer;transition:background .15s;flex-shrink:0;}
.book-btn:hover{background:#534AB7;}
.empty-msg{text-align:center;color:#9ca3c0;padding:2rem;font-size:13px;}

/* ── APPOINTMENTS PAGE ───────────────────────────────────────────────── */
.page-hdr{display:flex;align-items:center;margin-bottom:18px;}
.page-hdr h2{font-size:16px;font-weight:700;color:#2C2F3F;}
.sum-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
.sum-card{background:#fff;border:1px solid #e8e4f8;border-radius:10px;padding:14px;display:flex;align-items:center;gap:12px;}
.sum-card .ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sum-card .ico i{font-size:18px;}
.sum-card .num{font-size:22px;font-weight:700;color:#2C2F3F;line-height:1.2;}
.sum-card .lbl{font-size:11px;color:#9ca3c0;}
.tab-row{display:flex;gap:4px;border-bottom:2px solid #ede9fb;margin-bottom:18px;}
.tab{padding:9px 18px;font-size:13px;color:#9ca3c0;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;}
.tab:hover{color:#534AB7;}
.tab.active{color:#6C4FC4;border-bottom:2px solid #6C4FC4;font-weight:700;}
.toolbar{display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
.toolbar input{flex:1;min-width:160px;background:#fff;border:1px solid #e0daf5;border-radius:8px;padding:9px 14px;font-size:13px;color:#2C2F3F;outline:none;}
.toolbar input:focus{border-color:#7c6ee0;}
.fselect{background:#fff;border:1px solid #e0daf5;border-radius:8px;padding:9px 10px;font-size:12px;color:#2C2F3F;cursor:pointer;outline:none;}
.tbl-wrap{background:#fff;border-radius:12px;border:1px solid #ede9fb;overflow:hidden;}
table{width:100%;border-collapse:collapse;}
thead tr{background:#f4f0ff;}
th{padding:12px 14px;font-size:12px;font-weight:700;color:#534AB7;text-align:left;white-space:nowrap;}
td{padding:12px 14px;font-size:13px;color:#374151;border-top:1px solid #f0edf8;}
tr:hover td{background:#faf8ff;}
.badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;}
.badge.pending{background:#FEF3C7;color:#92400E;}
.badge.confirmed{background:#D1FAE5;color:#065F46;}
.badge.cancelled{background:#FEE2E2;color:#991B1B;}
.badge.completed{background:#DBEAFE;color:#1E40AF;}
.act-btn{background:none;border:1px solid #e0daf5;border-radius:6px;padding:5px 11px;font-size:12px;cursor:pointer;color:#534AB7;margin-right:4px;transition:all .15s;}
.act-btn:hover{background:#EEEDFE;border-color:#7c6ee0;}
.act-btn.danger{color:#991B1B;border-color:#fecaca;}
.act-btn.danger:hover{background:#FEE2E2;}
.avatar{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0;}
.pat-cell{display:flex;align-items:center;gap:10px;}
.empty-row td{text-align:center;padding:3rem;color:#9ca3c0;font-size:13px;}

/* ── BOOKING MODAL ───────────────────────────────────────────────────── */
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:1000;background:rgba(44,47,63,0.55);align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:14px;width:100%;max-width:460px;overflow:hidden;box-shadow:0 24px 70px rgba(108,79,196,0.2);}
.modal-top{background:#6C4FC4;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;}
.modal-top span{color:#fff;font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px;}
.modal-close{background:none;border:none;color:#fff;font-size:20px;cursor:pointer;opacity:0.8;display:flex;align-items:center;}
.modal-close:hover{opacity:1;}
.modal-body{padding:20px;max-height:80vh;overflow-y:auto;}
.hosp-sel{display:flex;align-items:center;gap:12px;background:#EEEDFE;border-radius:9px;padding:12px 16px;margin-bottom:18px;}
.hosp-sel i{font-size:22px;color:#534AB7;flex-shrink:0;}
.hosp-sel strong{font-size:13px;color:#3C3489;display:block;}
.hosp-sel small{font-size:12px;color:#7c6ee0;}
.field-lbl{font-size:12px;color:#9ca3c0;margin-bottom:8px;margin-top:16px;display:flex;align-items:center;gap:6px;}
.calendar{background:#f9f8fe;border-radius:10px;padding:14px;}
.cal-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.cal-nav{background:none;border:none;color:#534AB7;cursor:pointer;font-size:18px;padding:2px 8px;border-radius:6px;transition:background .15s;}
.cal-nav:hover{background:#EEEDFE;}
.cal-month-lbl{font-size:14px;font-weight:700;color:#2C2F3F;}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;}
.cal-dow{font-size:11px;color:#9ca3c0;padding:3px 0;font-weight:600;}
.cal-day{font-size:12px;padding:7px 2px;border-radius:7px;cursor:pointer;color:#2C2F3F;transition:all .12s;}
.cal-day:hover{background:#EEEDFE;color:#534AB7;}
.cal-day.selected{background:#6C4FC4;color:#fff;font-weight:700;}
.cal-day.disabled{color:#d1d5db;cursor:default;}
.cal-day.disabled:hover{background:none;color:#d1d5db;}
.time-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:4px;}
.time-slot{font-size:12px;padding:8px 4px;text-align:center;border:1px solid #e0daf5;border-radius:7px;cursor:pointer;background:#fff;color:#2C2F3F;transition:all .12s;}
.time-slot:hover{border-color:#7c6ee0;color:#534AB7;background:#EEEDFE;}
.time-slot.selected{background:#6C4FC4;color:#fff;border-color:#6C4FC4;font-weight:700;}
.time-slot.taken{background:#f9f8fe;color:#d1d5db;cursor:default;text-decoration:line-through;}
.time-slot.taken:hover{background:#f9f8fe;color:#d1d5db;border-color:#e0daf5;}
.confirm-btn{width:100%;background:#6C4FC4;color:#fff;border:none;padding:12px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;margin-top:20px;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s;}
.confirm-btn:hover{background:#534AB7;}
.confirm-btn:disabled{opacity:0.4;cursor:default;}
.confirm-btn:disabled:hover{background:#6C4FC4;}
.success-box{text-align:center;padding:34px 20px;}
.success-ico{width:60px;height:60px;border-radius:50%;background:#EAF3DE;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;}
.success-ico i{font-size:28px;color:#3B6D11;}
.success-box h3{font-size:17px;font-weight:700;color:#2C2F3F;margin-bottom:10px;}
.success-box p{font-size:13px;color:#6b7280;line-height:1.9;margin-bottom:20px;}
.done-btn{background:#6C4FC4;color:#fff;border:none;padding:10px 30px;border-radius:7px;font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;}
.done-btn:hover{background:#534AB7;}

@media(max-width:768px){
  .status-grid,.sum-cards{grid-template-columns:repeat(2,1fr);}
  .sidebar{display:none;}
  .time-grid{grid-template-columns:repeat(3,1fr);}
  .welcome-box{flex-direction:column;}
  .field-row{flex-direction:column;gap:0;}
}
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     LOGIN / REGISTER PAGE
════════════════════════════════════════════════════════ -->
<div id="loginPage">
  <div class="login-page">
    <div class="login-card">

      <!-- Branding -->
      <div class="login-logo">
        <i class="ti ti-building-hospital"></i>
        <span>HealthCare</span>
      </div>
      <div class="login-title">Doctor Portal</div>
      <div class="login-sub" id="authSub">Sign in with your doctor credentials</div>

      <!-- Tabs -->
      <div class="auth-tabs">
        <button class="auth-tab active" id="tabLogin"    onclick="switchAuthTab('login')">Sign In</button>
        <button class="auth-tab"        id="tabRegister" onclick="switchAuthTab('register')">Register New Doctor</button>
      </div>

      <!-- ── SIGN IN PANEL ── -->
      <div class="auth-panel active" id="panelLogin">

        <div id="loginAlertBox"></div>

        <div class="field">
          <label>Username</label>
          <div class="field-wrap">
            <i class="ti ti-user fi"></i>
            <input type="text" id="usernameInput" placeholder="Enter your username" oninput="clearLoginErr()"/>
          </div>
        </div>

        <div class="field">
          <label>Password</label>
          <div class="field-wrap">
            <i class="ti ti-lock fi"></i>
            <input type="password" id="passwordInput" placeholder="Enter your password" oninput="clearLoginErr()" onkeydown="if(event.key==='Enter')function doLogin() {()"/>
            <button class="eye-btn" onclick="togglePwd('passwordInput','eyeIcon')" type="button">
              <i class="ti ti-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div class="err-msg" id="loginError" style="display:none;">
            <i class="ti ti-alert-circle"></i> Invalid username or password.
          </div>
        </div>

        <button class="login-btn" onclick="doLogin()">
          <i class="ti ti-login"></i> Sign In
        </button>

        <div class="divider"><span>Demo Accounts</span></div>
        <div class="demo-accounts">
          <p><i class="ti ti-info-circle"></i> Click "Use" to auto-fill credentials</p>
          <div class="demo-item">
            <div class="demo-info">
              <div class="demo-avatar" style="background:#6C4FC4;">AJ</div>
              <div><div class="demo-name">Dr. Adam Johnson</div><div class="demo-spec">Cardiologist</div></div>
            </div>
            <div style="display:flex;align-items:center;">
              <div class="demo-creds"><strong>dr.adam</strong>pass123</div>
              <button class="use-btn" onclick="fillLogin('dr.adam','pass123')">Use</button>
            </div>
          </div>
          <div class="demo-item">
            <div class="demo-info">
              <div class="demo-avatar" style="background:#0EA5E9;">PM</div>
              <div><div class="demo-name">Dr. Priya Mendis</div><div class="demo-spec">Neurologist</div></div>
            </div>
            <div style="display:flex;align-items:center;">
              <div class="demo-creds"><strong>dr.priya</strong>pass123</div>
              <button class="use-btn" onclick="fillLogin('dr.priya','pass123')">Use</button>
            </div>
          </div>
          <div class="demo-item">
            <div class="demo-info">
              <div class="demo-avatar" style="background:#10B981;">NF</div>
              <div><div class="demo-name">Dr. Nimal Fernando</div><div class="demo-spec">General Surgeon</div></div>
            </div>
            <div style="display:flex;align-items:center;">
              <div class="demo-creds"><strong>dr.nimal</strong>pass123</div>
              <button class="use-btn" onclick="fillLogin('dr.nimal','pass123')">Use</button>
            </div>
          </div>
        </div>
      </div><!-- /panelLogin -->

      <!-- ── REGISTER PANEL ── -->
      <div class="auth-panel" id="panelRegister">

        <div id="regAlertBox"></div>

        <p class="sec-lbl">Personal Information</p>
        <div class="field-row">
          <div class="field">
            <label>First Name <span class="req">*</span></label>
            <div class="field-wrap">
              <i class="ti ti-user fi"></i>
              <input type="text" id="reg_first" placeholder="First name" oninput="clearRegErr('reg_first')"/>
            </div>
            <div class="err-msg" id="err_first" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
          </div>
          <div class="field">
            <label>Last Name <span class="req">*</span></label>
            <div class="field-wrap">
              <i class="ti ti-user fi"></i>
              <input type="text" id="reg_last" placeholder="Last name" oninput="clearRegErr('reg_last')"/>
            </div>
            <div class="err-msg" id="err_last" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
          </div>
        </div>

        <hr class="sec-divider">
        <p class="sec-lbl">Account Details</p>

        <div class="field">
          <label>Username <span class="req">*</span></label>
          <div class="field-wrap">
            <i class="ti ti-at fi"></i>
            <input type="text" id="reg_username" placeholder="e.g. dr.john" oninput="clearRegErr('reg_username')"/>
          </div>
          <div class="err-msg" id="err_username" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
        </div>

        <div class="field">
          <label>Email Address <span class="req">*</span></label>
          <div class="field-wrap">
            <i class="ti ti-mail fi"></i>
            <input type="email" id="reg_email" placeholder="doctor@hospital.lk" oninput="clearRegErr('reg_email')"/>
          </div>
          <div class="err-msg" id="err_email" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
        </div>

        <div class="field">
          <label>Phone Number <span class="req">*</span></label>
          <div class="field-wrap">
            <i class="ti ti-phone fi"></i>
            <input type="tel" id="reg_phone" placeholder="+94 77 123 4567" oninput="clearRegErr('reg_phone')"/>
          </div>
          <div class="err-msg" id="err_phone" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
        </div>

        <hr class="sec-divider">
        <p class="sec-lbl">Medical Information</p>

        <div class="field">
          <label>Specialty <span class="req">*</span></label>
          <div class="field-wrap">
            <i class="ti ti-stethoscope fi"></i>
            <select id="reg_specialty" onchange="clearRegErr('reg_specialty')">
              <option value="">— Select specialty —</option>
              <option>Cardiology</option><option>Dermatology</option>
              <option>Emergency Medicine</option><option>Endocrinology</option>
              <option>Gastroenterology</option><option>General Practice</option>
              <option>General Surgery</option><option>Neurology</option>
              <option>Obstetrics & Gynecology</option><option>Oncology</option>
              <option>Ophthalmology</option><option>Orthopedics</option>
              <option>Pediatrics</option><option>Psychiatry</option>
              <option>Radiology</option><option>Surgery</option><option>Urology</option>
            </select>
          </div>
          <div class="err-msg" id="err_specialty" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
        </div>

        <hr class="sec-divider">
        <p class="sec-lbl">Set Password</p>

        <div class="field">
          <label>Password <span class="req">*</span></label>
          <div class="field-wrap">
            <i class="ti ti-lock fi"></i>
            <input type="password" id="reg_password" placeholder="Min. 6 characters" oninput="clearRegErr('reg_password')"/>
            <button class="eye-btn" onclick="togglePwd('reg_password','regEyeIcon1')" type="button">
              <i class="ti ti-eye" id="regEyeIcon1"></i>
            </button>
          </div>
          <div class="err-msg" id="err_password" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
        </div>

        <div class="field">
          <label>Confirm Password <span class="req">*</span></label>
          <div class="field-wrap">
            <i class="ti ti-lock fi"></i>
            <input type="password" id="reg_confirm" placeholder="Re-enter password" oninput="clearRegErr('reg_confirm')"/>
            <button class="eye-btn" onclick="togglePwd('reg_confirm','regEyeIcon2')" type="button">
              <i class="ti ti-eye" id="regEyeIcon2"></i>
            </button>
          </div>
          <div class="err-msg" id="err_confirm" style="display:none;"><i class="ti ti-alert-circle"></i> <span></span></div>
        </div>

        <button class="login-btn" onclick="doRegister()">
          <i class="ti ti-user-plus"></i> Register &amp; Sign In
        </button>

      </div><!-- /panelRegister -->

    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     DOCTOR DASHBOARD
════════════════════════════════════════════════════════ -->
<div id="dashApp" style="display:none;">
  <div class="app">
    <div class="topbar">
      <div class="topbar-brand"><i class="ti ti-building-hospital"></i>HealthCare Doctor</div>
      <div class="topbar-right">
        <div class="topbar-avatar" id="topbarAvatar"></div>
        <span id="topbarName"></span>
        <button class="logout-btn" onclick="doLogout()"><i class="ti ti-logout"></i> Logout</button>
      </div>
    </div>
    <div class="body">
      <div class="sidebar">
        <div class="nav-item" id="snav-dashboard"    onclick="showPage('dashboard')"><i class="ti ti-layout-dashboard"></i>Dashboard</div>
        <div class="nav-item" id="snav-appointments" onclick="showPage('appointments')"><i class="ti ti-calendar"></i>My Appointments</div>
        <div class="nav-item"><i class="ti ti-video"></i>My Sessions</div>
        <div class="nav-item"><i class="ti ti-users"></i>My Patients</div>
        <div class="nav-item"><i class="ti ti-settings"></i>Settings</div>
      </div>
      <div class="content" id="mainContent"></div>
    </div>
  </div>
</div>

<!-- Booking Modal -->
<div class="modal-overlay" id="bookingModal">
  <div class="modal">
    <div class="modal-top">
      <span><i class="ti ti-calendar"></i> Book Appointment</span>
      <button class="modal-close" onclick="closeModal()"><i class="ti ti-x"></i></button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<script>
// ════════════════════════════════════════════════════════
// DATA
// ════════════════════════════════════════════════════════
const doctors = [
  {username:"dr.adam",  password:"pass123", name:"Dr. Adam Johnson",   specialty:"Cardiologist",    initials:"AJ", color:"#6C4FC4"},
  {username:"dr.priya", password:"pass123", name:"Dr. Priya Mendis",   specialty:"Neurologist",     initials:"PM", color:"#0EA5E9"},
  {username:"dr.nimal", password:"pass123", name:"Dr. Nimal Fernando", specialty:"General Surgeon", initials:"NF", color:"#10B981"},
];

const allAppointments = {
  "dr.adam": [
    {id:"APT001",patient:"Kamal Perera",      age:34,hospital:"Nawaloka Hospital",              city:"Colombo",   date:"2026-05-14",time:"8:00 AM", status:"confirmed",color:"#6C4FC4"},
    {id:"APT002",patient:"Sunethra Silva",    age:28,hospital:"Asiri Central Hospital",          city:"Colombo",   date:"2026-05-14",time:"9:30 AM", status:"pending",  color:"#0EA5E9"},
    {id:"APT003",patient:"Amali Wickrama",    age:41,hospital:"National Hospital of Sri Lanka",  city:"Colombo",   date:"2026-05-15",time:"11:00 AM",status:"pending",  color:"#F59E0B"},
    {id:"APT004",patient:"Rohan Jayasekara",  age:60,hospital:"Durdans Hospital",                city:"Colombo",   date:"2026-05-16",time:"8:30 AM", status:"completed",color:"#8B5CF6"},
    {id:"APT005",patient:"Chamari Bandara",   age:37,hospital:"Colombo South Teaching Hospital", city:"Colombo",   date:"2026-05-17",time:"3:00 PM", status:"confirmed",color:"#EC4899"},
  ],
  "dr.priya": [
    {id:"APT006",patient:"Nimal Fernando",    age:52,hospital:"Teaching Hospital Kandy",         city:"Kandy",     date:"2026-05-14",time:"10:00 AM",status:"confirmed",color:"#10B981"},
    {id:"APT007",patient:"Dilshan Rathnayake",age:45,hospital:"Jaffna Teaching Hospital",        city:"Jaffna",    date:"2026-05-15",time:"10:30 AM",status:"pending",  color:"#14B8A6"},
    {id:"APT008",patient:"Priya Mendis",      age:22,hospital:"Lanka Hospitals",                 city:"Colombo",   date:"2026-05-16",time:"2:00 PM", status:"cancelled",color:"#EF4444"},
    {id:"APT009",patient:"Saman Kumara",      age:48,hospital:"Kurunegala Teaching Hospital",    city:"Kurunegala",date:"2026-05-17",time:"9:00 AM", status:"pending",  color:"#F97316"},
  ],
  "dr.nimal": [
    {id:"APT010",patient:"Ruwan Perera",      age:55,hospital:"Teaching Hospital Karapitiya",    city:"Galle",     date:"2026-05-14",time:"8:00 AM", status:"confirmed",color:"#6C4FC4"},
    {id:"APT011",patient:"Lakshmi Raj",       age:31,hospital:"National Hospital of Sri Lanka",  city:"Colombo",   date:"2026-05-15",time:"2:30 PM", status:"pending",  color:"#0EA5E9"},
    {id:"APT012",patient:"Chathura Silva",    age:29,hospital:"Asiri Central Hospital",          city:"Colombo",   date:"2026-05-16",time:"11:00 AM",status:"confirmed",color:"#10B981"},
  ],
};

const hospitals = [
  {name:"National Hospital of Sri Lanka",      city:"Colombo",    specialty:"General",        beds:3500},
  {name:"Colombo South Teaching Hospital",     city:"Colombo",    specialty:"General",        beds:1200},
  {name:"Nawaloka Hospital",                   city:"Colombo",    specialty:"Multi-specialty",beds:450},
  {name:"Lanka Hospitals",                     city:"Colombo",    specialty:"Multi-specialty",beds:350},
  {name:"Asiri Central Hospital",              city:"Colombo",    specialty:"Multi-specialty",beds:500},
  {name:"Teaching Hospital Kandy",             city:"Kandy",      specialty:"General",        beds:1800},
  {name:"Durdans Hospital",                    city:"Colombo",    specialty:"Multi-specialty",beds:300},
  {name:"Teaching Hospital Karapitiya",        city:"Galle",      specialty:"General",        beds:1400},
  {name:"Jaffna Teaching Hospital",            city:"Jaffna",     specialty:"General",        beds:900},
  {name:"Kurunegala Teaching Hospital",        city:"Kurunegala", specialty:"General",        beds:1100},
];

const cities     = ["All",...new Set(hospitals.map(h=>h.city))];
const timeSlots  = ["8:00 AM","8:30 AM","9:00 AM","9:30 AM","10:00 AM","10:30 AM","11:00 AM","11:30 AM","2:00 PM","2:30 PM","3:00 PM","3:30 PM"];
const takenSlots = ["9:00 AM","10:30 AM","2:30 PM"];

// Avatar colors for newly registered doctors
const avatarColors = ["#6C4FC4","#0EA5E9","#10B981","#F59E0B","#EF4444","#8B5CF6","#EC4899","#14B8A6","#F97316"];

// ════════════════════════════════════════════════════════
// STATE
// ════════════════════════════════════════════════════════
let currentDoctor = null;
let showPwdMap    = {};
let showInline    = false;
let activeCity    = "All";
let hospSearch    = "";
let apptTab       = "all";
let apptSearch    = "";
let apptStatus    = "all";
let apptHosp      = "All";
let apptCity      = "All";
let selHospital   = null;
let selDate       = null;
let selTimeVal    = null;
let calYear, calMonth;

// ════════════════════════════════════════════════════════
// AUTH TABS
// ════════════════════════════════════════════════════════
function switchAuthTab(tab) {
  document.getElementById('tabLogin').classList.toggle('active',    tab==='login');
  document.getElementById('tabRegister').classList.toggle('active', tab==='register');
  document.getElementById('panelLogin').classList.toggle('active',    tab==='login');
  document.getElementById('panelRegister').classList.toggle('active', tab==='register');
  document.getElementById('authSub').textContent = tab==='login'
    ? 'Sign in with your doctor credentials'
    : 'Create a new doctor account';
  document.getElementById('loginAlertBox').innerHTML = '';
  document.getElementById('regAlertBox').innerHTML   = '';
}

// ════════════════════════════════════════════════════════
// LOGIN
// ════════════════════════════════════════════════════════
function fillLogin(u, p) {
  document.getElementById("usernameInput").value = u;
  document.getElementById("passwordInput").value = p;
  clearLoginErr();
}


 function doLogin() {
  const u = document.getElementById("usernameInput").value.trim().toLowerCase();
  const p = document.getElementById("passwordInput").value;

  fetch('api_login.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({username:u, password:p})
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      loginAs(result.doctor);
    } else {
      document.getElementById("loginError").style.display = "flex";
      document.getElementById("usernameInput").classList.add("err");
      document.getElementById("passwordInput").classList.add("err");
    }
  });
}


function loginAs(doc) {
  currentDoctor = doc;
  document.getElementById("loginPage").style.display  = "none";
  document.getElementById("dashApp").style.display    = "block";
  document.getElementById("topbarName").textContent   = doc.name;
  document.getElementById("topbarAvatar").textContent = doc.initials;
  document.getElementById("topbarAvatar").style.background = doc.color;
  apptTab="all"; apptSearch=""; apptStatus="all"; apptHosp="All"; apptCity="All";
  showInline=false; activeCity="All"; hospSearch="";
  showPage("dashboard");
}

function doLogout() {
  currentDoctor = null;
  document.getElementById("dashApp").style.display  = "none";
  document.getElementById("loginPage").style.display = "block";
  document.getElementById("usernameInput").value = "";
  document.getElementById("passwordInput").value = "";
  clearLoginErr();
  switchAuthTab('login');
}

function clearLoginErr() {
  document.getElementById("loginError").style.display = "none";
  document.getElementById("usernameInput").classList.remove("err");
  document.getElementById("passwordInput").classList.remove("err");
}

// ════════════════════════════════════════════════════════
// REGISTER
// ════════════════════════════════════════════════════════
function doRegister() {
  // Gather values
  const first    = document.getElementById('reg_first').value.trim();
  const last     = document.getElementById('reg_last').value.trim();
  const username = document.getElementById('reg_username').value.trim().toLowerCase();
  const email    = document.getElementById('reg_email').value.trim();
  const phone    = document.getElementById('reg_phone').value.trim();
  const specialty= document.getElementById('reg_specialty').value;
  const password = document.getElementById('reg_password').value;
  const confirm  = document.getElementById('reg_confirm').value;

  let valid = true;

  function setErr(fieldId, errId, msg) {
    document.getElementById(fieldId).classList.add('err');
    const box = document.getElementById(errId);
    box.style.display = 'flex';
    box.querySelector('span').textContent = msg;
    valid = false;
  }

  // Clear all errors first
  ['reg_first','reg_last','reg_username','reg_email','reg_phone','reg_specialty','reg_password','reg_confirm']
    .forEach(id => { const el=document.getElementById(id); if(el) el.classList.remove('err'); });
  ['err_first','err_last','err_username','err_email','err_phone','err_specialty','err_password','err_confirm']
    .forEach(id => { document.getElementById(id).style.display='none'; });
  document.getElementById('regAlertBox').innerHTML = '';

  // Validate
  if (!first)    setErr('reg_first',    'err_first',    'First name is required.');
  if (!last)     setErr('reg_last',     'err_last',     'Last name is required.');

  if (!username) {
    setErr('reg_username', 'err_username', 'Username is required.');
  } else if (doctors.find(d => d.username === username)) {
    setErr('reg_username', 'err_username', 'Username already taken.');
  }

  if (!email) {
    setErr('reg_email', 'err_email', 'Email is required.');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    setErr('reg_email', 'err_email', 'Enter a valid email address.');
  }

  if (!phone) {
    setErr('reg_phone', 'err_phone', 'Phone number is required.');
  } else if (!/^\+?[\d\s\-\(\)]{7,20}$/.test(phone)) {
    setErr('reg_phone', 'err_phone', 'Enter a valid phone number.');
  }

  if (!specialty) setErr('reg_specialty', 'err_specialty', 'Please select a specialty.');

  if (!password) {
    setErr('reg_password', 'err_password', 'Password is required.');
  } else if (password.length < 6) {
    setErr('reg_password', 'err_password', 'Password must be at least 6 characters.');
  } else if (password !== confirm) {
    setErr('reg_confirm', 'err_confirm', 'Passwords do not match.');
  }

  if (!valid) {
    document.getElementById('regAlertBox').innerHTML =
      `<div class="alert-box alert-danger"><i class="ti ti-alert-circle"></i>Please fix the errors below.</div>`;
    return;
  }

  // Build new doctor object
  const initls = (first[0]+(last[0]||'')).toUpperCase();
  const color  = avatarColors[doctors.length % avatarColors.length];
  const newDoc = {
    username, password, name:`Dr. ${first} ${last}`,
    specialty, initials: initls, color
  };

  fetch('api_register.php', {
  method: 'POST',
  headers: {'Content-Type':'application/json'},
  body: JSON.stringify({first, last, username, email, phone, specialty, password})
})
.then(res => res.json())
.then(result => {
  if (result.success) {
    allAppointments[result.doctor.username] = [];
    loginAs(result.doctor);
  } else {
    document.getElementById('regAlertBox').innerHTML =
      `<div class="alert-box alert-danger"><i class="ti ti-alert-circle"></i>${result.message}</div>`;
  }
});
}

function clearRegErr(fieldId) {
  document.getElementById(fieldId).classList.remove('err');
  const errMap = {
    reg_first:'err_first', reg_last:'err_last', reg_username:'err_username',
    reg_email:'err_email', reg_phone:'err_phone', reg_specialty:'err_specialty',
    reg_password:'err_password', reg_confirm:'err_confirm'
  };
  if (errMap[fieldId]) document.getElementById(errMap[fieldId]).style.display = 'none';
}

// ════════════════════════════════════════════════════════
// TOGGLE PASSWORD VISIBILITY
// ════════════════════════════════════════════════════════
function togglePwd(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  const hide  = input.type === 'password';
  input.type  = hide ? 'text' : 'password';
  icon.className = hide ? 'ti ti-eye-off' : 'ti ti-eye';
}

// ════════════════════════════════════════════════════════
// NAVIGATION
// ════════════════════════════════════════════════════════
function showPage(p) {
  document.querySelectorAll(".nav-item").forEach(el => el.classList.remove("active"));
  const el = document.getElementById("snav-" + p);
  if (el) el.classList.add("active");
  if (p === "dashboard")    renderDashboard();
  if (p === "appointments") renderApptPage();
}

// ════════════════════════════════════════════════════════
// DASHBOARD
// ════════════════════════════════════════════════════════
function renderDashboard() {
  const appts    = allAppointments[currentDoctor.username] || [];
  const today    = appts.filter(a => a.date === "2026-05-14").length;
  const pending  = appts.filter(a => a.status === "pending").length;
  const confirmed= appts.filter(a => a.status === "confirmed").length;
  const total    = appts.length;

  document.getElementById("mainContent").innerHTML = `
    <div class="section-title">Dashboard</div>
    <div class="welcome-box">
      <div class="welcome-box-left">
        <h3>Welcome, ${currentDoctor.name}!</h3>
        <p>You have <strong>${today}</strong> appointment${today!==1?"s":""} today and
        <strong>${pending}</strong> pending booking${pending!==1?"s":""} awaiting your confirmation.</p>
        <button class="view-btn" onclick="toggleInline(true)">
          <i class="ti ti-calendar"></i> View My Appointments
        </button>
      </div>
      <div class="welcome-doc-badge">
        <div class="ava" style="background:${currentDoctor.color};">${currentDoctor.initials}</div>
        <div class="info">
          <strong>${currentDoctor.name}</strong>
          <small>${currentDoctor.specialty}</small>
        </div>
      </div>
    </div>

    <div class="section-title">Status</div>
    <div class="status-grid">
      <div class="stat-card" onclick="showPage('appointments')">
        <div class="stat-icon"><i class="ti ti-calendar"></i></div>
        <div class="stat-num">${total}</div><div class="stat-label">Total Appointments</div>
      </div>
      <div class="stat-card" onclick="showPage('appointments')">
        <div class="stat-icon"><i class="ti ti-clock"></i></div>
        <div class="stat-num">${today}</div><div class="stat-label">Today</div>
      </div>
      <div class="stat-card hl" onclick="showPage('appointments')">
        <div class="stat-icon"><i class="ti ti-hourglass"></i></div>
        <div class="stat-num">${pending}</div><div class="stat-label">Pending</div>
      </div>
      <div class="stat-card" onclick="showPage('appointments')">
        <div class="stat-icon"><i class="ti ti-circle-check"></i></div>
        <div class="stat-num">${confirmed}</div><div class="stat-label">Confirmed</div>
      </div>
    </div>

    <div id="inlineAppt" style="display:${showInline?"block":"none"};">
      <div class="appt-box">
        <div class="appt-hdr">
          <span><i class="ti ti-building-hospital"></i> Select Hospital for Appointment</span>
          <button class="hide-btn" onclick="toggleInline(false)">
            <i class="ti ti-arrow-left"></i> Hide
          </button>
        </div>
        <div class="appt-bdy">
          <div class="search-row">
            <i class="ti ti-search" style="font-size:18px;color:#9ca3c0;"></i>
            <input type="text" id="hospSearchInput" placeholder="Search hospital or city..." value="${hospSearch}" oninput="setHospSearch(this.value)"/>
          </div>
          <div class="city-pills" id="cityPills"></div>
          <div class="count-lbl" id="hospCount"></div>
          <div id="hospList"></div>
        </div>
      </div>
    </div>
  `;
  if (showInline) { buildCityPills(); renderHospList(); }
}

function toggleInline(show) {
  showInline = show;
  if (show) { activeCity = "All"; hospSearch = ""; }
  renderDashboard();
}
function setHospSearch(v) { hospSearch = v; buildCityPills(); renderHospList(); }
function buildCityPills() {
  document.getElementById("cityPills").innerHTML = cities.map(c =>
    `<button class="pill${c===activeCity?" active":""}" onclick="setHospCity('${c}')">${c}</button>`
  ).join("");
}
function setHospCity(c) { activeCity = c; buildCityPills(); renderHospList(); }
function renderHospList() {
  const q = hospSearch.toLowerCase();
  const filtered = hospitals.filter(h =>
    (activeCity==="All"||h.city===activeCity) &&
    (h.name.toLowerCase().includes(q)||h.city.toLowerCase().includes(q))
  );
  document.getElementById("hospCount").textContent = `${filtered.length} hospital${filtered.length!==1?"s":""} found`;
  document.getElementById("hospList").innerHTML = filtered.length
    ? filtered.map(h=>`
      <div class="hosp-card">
        <div class="hc-left">
          <div class="hc-ico"><i class="ti ti-building-hospital"></i></div>
          <div>
            <div class="hc-name">${h.name}<span class="city-badge">${h.city}</span></div>
            <div class="hc-meta">${h.specialty} &middot; ${h.beds.toLocaleString()} beds</div>
          </div>
        </div>
        <button class="book-btn" onclick='openBooking(${JSON.stringify(h)})'>Book ↗</button>
      </div>
    `).join("")
    : `<div class="empty-msg"><i class="ti ti-building-hospital" style="font-size:28px;display:block;margin-bottom:8px;"></i>No hospitals found.</div>`;
}

// ════════════════════════════════════════════════════════
// APPOINTMENTS PAGE
// ════════════════════════════════════════════════════════
function renderApptPage() {
  const appts   = allAppointments[currentDoctor.username] || [];
  const total   = appts.length;
  const todayCnt= appts.filter(a=>a.date==="2026-05-14").length;
  const pendCnt = appts.filter(a=>a.status==="pending").length;
  const compCnt = appts.filter(a=>a.status==="completed").length;

  const filtered = appts.filter(a => {
    const mt = apptTab==="all"
      ||(apptTab==="today"&&a.date==="2026-05-14")
      ||(apptTab==="upcoming"&&a.date>"2026-05-14")
      ||(apptTab==="completed"&&a.status==="completed");
    const ms = apptStatus==="all"||a.status===apptStatus;
    const mc = apptCity==="All"||a.city===apptCity;
    const mh = apptHosp==="All"||a.hospital===apptHosp;
    const mq = !apptSearch
      ||a.patient.toLowerCase().includes(apptSearch)
      ||a.hospital.toLowerCase().includes(apptSearch)
      ||a.city.toLowerCase().includes(apptSearch);
    return mt&&ms&&mc&&mh&&mq;
  });

  const cityOpts = ["All",...new Set(appts.map(a=>a.city))].map(c=>
    `<option value="${c}" ${apptCity===c?"selected":""}>${c==="All"?"All Cities":c}</option>`).join("");
  const hospOpts = ["All",...new Set(appts.map(a=>a.hospital))].map(h=>
    `<option value="${h}" ${apptHosp===h?"selected":""}>${h==="All"?"All Hospitals":h}</option>`).join("");
  const statOpts = ["all","pending","confirmed","completed","cancelled"].map(s=>
    `<option value="${s}" ${apptStatus===s?"selected":""}>${s==="all"?"All Status":cap(s)}</option>`).join("");

  const rows = filtered.length
    ? filtered.map(a=>`
      <tr>
        <td><span style="font-size:11px;color:#9ca3c0;">${a.id}</span></td>
        <td>
          <div class="pat-cell">
            <div class="avatar" style="background:${a.color};">${initials(a.patient)}</div>
            <div>
              <div style="font-weight:600;">${a.patient}</div>
              <div style="font-size:11px;color:#9ca3c0;">Age ${a.age}</div>
            </div>
          </div>
        </td>
        <td>
          <div style="font-weight:600;">${a.hospital}</div>
          <div style="font-size:11px;color:#9ca3c0;">${a.city}</div>
        </td>
        <td>
          <div style="font-weight:600;">${fmtDate(a.date)}</div>
          <div style="font-size:11px;color:#9ca3c0;">${a.time}</div>
        </td>
        <td><span class="badge ${a.status}">${cap(a.status)}</span></td>
        <td>
          ${a.status==="pending"
            ?`<button class="act-btn" onclick="updStatus('${a.id}','confirmed')"><i class="ti ti-check"></i> Confirm</button>
              <button class="act-btn danger" onclick="updStatus('${a.id}','cancelled')"><i class="ti ti-x"></i> Cancel</button>`:""}
          ${a.status==="confirmed"
            ?`<button class="act-btn" onclick="updStatus('${a.id}','completed')"><i class="ti ti-check-check"></i> Done</button>`:""}
          ${a.status==="completed"||a.status==="cancelled"
            ?`<span style="font-size:11px;color:#9ca3c0;">—</span>`:""}
        </td>
      </tr>
    `).join("")
    : (appts.length === 0
      ? `<tr class="empty-row"><td colspan="6">
           <i class="ti ti-calendar-plus" style="font-size:28px;display:block;margin-bottom:10px;color:#c5bff0;"></i>
           No appointments yet. Go to Dashboard to book your first appointment.
         </td></tr>`
      : `<tr class="empty-row"><td colspan="6">
           <i class="ti ti-calendar-off" style="font-size:28px;display:block;margin-bottom:10px;"></i>
           No appointments found.
         </td></tr>`);

  document.getElementById("mainContent").innerHTML = `
    <div class="page-hdr">
      <h2><i class="ti ti-calendar" style="font-size:17px;vertical-align:-2px;margin-right:8px;color:#6C4FC4;"></i>My Appointments</h2>
    </div>
    <div class="sum-cards">
      <div class="sum-card">
        <div class="ico" style="background:#EEEDFE;"><i class="ti ti-calendar" style="color:#6C4FC4;"></i></div>
        <div><div class="num">${total}</div><div class="lbl">Total</div></div>
      </div>
      <div class="sum-card">
        <div class="ico" style="background:#FEF3C7;"><i class="ti ti-clock" style="color:#92400E;"></i></div>
        <div><div class="num">${todayCnt}</div><div class="lbl">Today</div></div>
      </div>
      <div class="sum-card">
        <div class="ico" style="background:#FEE2E2;"><i class="ti ti-hourglass" style="color:#991B1B;"></i></div>
        <div><div class="num">${pendCnt}</div><div class="lbl">Pending</div></div>
      </div>
      <div class="sum-card">
        <div class="ico" style="background:#D1FAE5;"><i class="ti ti-circle-check" style="color:#065F46;"></i></div>
        <div><div class="num">${compCnt}</div><div class="lbl">Completed</div></div>
      </div>
    </div>
    <div class="tab-row">
      ${["all","today","upcoming","completed"].map(t=>
        `<div class="tab${apptTab===t?" active":""}" onclick="setTab('${t}')">${cap(t)}</div>`
      ).join("")}
    </div>
    <div class="toolbar">
      <i class="ti ti-search" style="font-size:18px;color:#9ca3c0;flex-shrink:0;"></i>
      <input type="text" placeholder="Search patient, hospital or city..." value="${apptSearch}" oninput="setSearch(this.value)"/>
      <select class="fselect" onchange="setCity(this.value)">${cityOpts}</select>
      <select class="fselect" onchange="setHosp(this.value)">${hospOpts}</select>
      <select class="fselect" onchange="setStat(this.value)">${statOpts}</select>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Patient</th><th>Hospital</th><th>Date &amp; Time</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
  `;
}

function setTab(t)    { apptTab=t;                  renderApptPage(); }
function setSearch(v) { apptSearch=v.toLowerCase(); renderApptPage(); }
function setCity(v)   { apptCity=v;                 renderApptPage(); }
function setHosp(v)   { apptHosp=v;                 renderApptPage(); }
function setStat(v)   { apptStatus=v;               renderApptPage(); }
function updStatus(id,s) {
  const a = (allAppointments[currentDoctor.username]||[]).find(x=>x.id===id);
  if(a){ a.status=s; renderApptPage(); }
}

// ════════════════════════════════════════════════════════
// BOOKING MODAL
// ════════════════════════════════════════════════════════
function openBooking(h) {
  selHospital=h; selDate=null; selTimeVal=null;
  const now=new Date(); calYear=now.getFullYear(); calMonth=now.getMonth();
  document.getElementById("bookingModal").classList.add("open");
  renderModal();
}
function closeModal(){ document.getElementById("bookingModal").classList.remove("open"); }
function renderModal() {
  const months=["January","February","March","April","May","June","July","August","September","October","November","December"];
  const now=new Date();
  const today=new Date(now.getFullYear(),now.getMonth(),now.getDate());
  const firstDay=new Date(calYear,calMonth,1).getDay();
  const daysInMonth=new Date(calYear,calMonth+1,0).getDate();
  const dows=["Su","Mo","Tu","We","Th","Fr","Sa"];
  let cells=dows.map(d=>`<div class="cal-dow">${d}</div>`).join("");
  for(let i=0;i<firstDay;i++) cells+=`<div></div>`;
  for(let d=1;d<=daysInMonth;d++){
    const dt=new Date(calYear,calMonth,d);
    const ds=`${calYear}-${String(calMonth+1).padStart(2,"0")}-${String(d).padStart(2,"0")}`;
    const past=dt<today; const sel=selDate===ds;
    cells+=`<div class="cal-day${past?" disabled":""}${sel?" selected":""}" ${past?"":"onclick=\"pickDay('"+ds+"')\""} >${d}</div>`;
  }
  const timeHTML=timeSlots.map(t=>{
    const taken=takenSlots.includes(t); const sel=selTimeVal===t;
    return `<div class="time-slot${taken?" taken":""}${sel?" selected":""}" ${taken?"":"onclick=\"pickTime('"+t+"')\""} >${t}</div>`;
  }).join("");
  document.getElementById("modalBody").innerHTML=`
    <div class="hosp-sel">
      <i class="ti ti-building-hospital"></i>
      <div><strong>${selHospital.name}</strong><small>${selHospital.city} &middot; ${selHospital.specialty}</small></div>
    </div>
    <div class="field-lbl"><i class="ti ti-calendar"></i> Select date</div>
    <div class="calendar">
      <div class="cal-hdr">
        <button class="cal-nav" onclick="chgMonth(-1)"><i class="ti ti-chevron-left"></i></button>
        <span class="cal-month-lbl">${months[calMonth]} ${calYear}</span>
        <button class="cal-nav" onclick="chgMonth(1)"><i class="ti ti-chevron-right"></i></button>
      </div>
      <div class="cal-grid">${cells}</div>
    </div>
    <div class="field-lbl"><i class="ti ti-clock"></i> Select time <span style="font-size:11px;color:#d1d5db;">(strikethrough = taken)</span></div>
    <div class="time-grid">${timeHTML}</div>
    <button class="confirm-btn" onclick="confirmBooking()" ${selDate&&selTimeVal?"":"disabled"}>
      <i class="ti ti-check"></i> Confirm Appointment
    </button>
  `;
}
function pickDay(ds){ selDate=ds; renderModal(); }
function pickTime(t){ selTimeVal=t; renderModal(); }
function chgMonth(d){
  calMonth+=d;
  if(calMonth>11){calMonth=0;calYear++;}
  if(calMonth<0){calMonth=11;calYear--;}
  renderModal();
}
function confirmBooking(){
  const months=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
  const [y,m,d]=selDate.split("-");
  const dateStr=`${parseInt(d)} ${months[parseInt(m)-1]} ${y}`;
  document.getElementById("modalBody").innerHTML=`
    <div class="success-box">
      <div class="success-ico"><i class="ti ti-check"></i></div>
      <h3>Appointment Booked!</h3>
      <p><strong>${selHospital.name}</strong><br>${selHospital.city} &middot; ${selHospital.specialty}<br><br>
      <i class="ti ti-calendar" style="font-size:13px;vertical-align:-1px;"></i> &nbsp;${dateStr}
      &nbsp;&nbsp;&nbsp;
      <i class="ti ti-clock" style="font-size:13px;vertical-align:-1px;"></i> &nbsp;${selTimeVal}</p>
      <button class="done-btn" onclick="closeModal()">Done</button>
    </div>
  `;
}

// ════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════
function initials(n){ return n.split(" ").map(x=>x[0]).join("").slice(0,2).toUpperCase(); }
function cap(s)     { return s.charAt(0).toUpperCase()+s.slice(1); }
function fmtDate(ds){
  const [y,m,d]=ds.split("-");
  const mo=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
  return `${parseInt(d)} ${mo[parseInt(m)-1]} ${y}`;
}

// Close modal on backdrop click
document.getElementById("bookingModal").addEventListener("click",function(e){
  if(e.target===this) closeModal();
});
</script>
</body>
</html>