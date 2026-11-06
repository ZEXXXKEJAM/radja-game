<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lihat Pesanan - Dashboard Admin</title>

  <!-- SEO Optimization -->
  <meta name="description" content="UI Forms - Spark Admin Premium Bootstrap 5 Admin Dashboard Template">
  <meta name="author" content="Spark Admin Team">

  <!-- Favicon -->
  <link rel="icon" type="/image/png" href="../img/logo.png">

  <!-- Local Third-Party Libraries (100% Offline Compatible) -->
  <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/libs/apexcharts/apexcharts.css">
  <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>

  <!-- ==========================================
         START: Sidebar Component
         Highly polished, dark-green navigation
         ========================================== -->
        
  <div class="sidebar-wrapper" id="sidebar">
    <!-- Brand Logo / Identity -->
    <a href="index.php" class="sidebar-brand">
      <i class="bi bi-asterisk"></i>
      <span>Dashboard Admin</span>
    </a>

    <!-- Navigation Menu -->
    <!-- <div class="flex-grow-1 overflow-y-auto"> -->
      <!-- Group: Menu -->
      <!-- <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Menu</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="index.php" class="sidebar-menu-link" id="menu-overview" title="Overview">
              <i class="bi bi-grid-fill"></i>
              <span>Dashboard</span>
            </a>
          </li>
        </ul>
      </div> -->

      <!-- Group: Components -->
      <div class="sidebar-menu-section">
        <!-- <div class="sidebar-menu-title">Components</div> -->
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="index.php" class="sidebar-menu-link" id="menu-uiforms" title="Dashboard Admin">
              <i class="bi bi-input-cursor-text"></i>
              <span>Dashboard Admin</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="kelola-produk.php" class="sidebar-menu-link" id="menu-basictables" title="Basic Tables">
              <i class="bi bi-table"></i>
              <span>Kelola Produk</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="lihat-pesanan.php" class="sidebar-menu-link active" id="menu-uiforms" title="Forms and Input">
              <i class="bi bi-input-cursor-text"></i>
              <span>Lihat Pesanan</span>
            </a>
         <li class="sidebar-menu-item">
            <a href="transaksi.php" class="sidebar-menu-link" id="menu-uiforms" title="">
              <i class="bi bi-input-cursor-text"></i>
              <span>Transaksi</span>
            </a>
          </li>
              <li class="sidebar-menu-item">
            <a href="supplier.php" class="sidebar-menu-link" id="menu-uiforms" title="">
              <i class="bi bi-input-cursor-text"></i>
              <span>Supplier</span>
            </a>
          </li>
          <!-- <li class="sidebar-menu-item">
            <a href="ui-buttons.html" class="sidebar-menu-link" id="menu-uibuttons" title="Buttons">
              <i class="bi bi-menu-button-wide-fill"></i>
              <span>Buttons & Alerts</span>
            </a>
          </li>
        </ul>
      </div> -->

      <!-- Group: Pages -->
      <!-- <div class="sidebar-menu-section">
        <div class="sidebar-menu-title">Pages</div>
        <ul class="sidebar-menu-list">
          <li class="sidebar-menu-item">
            <a href="page-blank.html" class="sidebar-menu-link" id="menu-blankpage" title="Blank Page">
              <i class="bi bi-file-earmark"></i>
              <span>Blank Page</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="page-login.html" class="sidebar-menu-link" id="menu-loginpage" title="Login Page">
              <i class="bi bi-box-arrow-in-right"></i>
              <span>Login Screen</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="page-404.html" class="sidebar-menu-link" id="menu-404" title="404 Page">
              <i class="bi bi-slash-circle"></i>
              <span>Error 404</span>
            </a>
          </li> -->
        </ul>
      </div>
    </div>

    <!-- Sidebar Profile Card (Dynamic Footer) -->
    <div class="sidebar-profile">
      <!-- <img src="assets/images/avatar.png" alt="Administrator" class="sidebar-profile-img"
        onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=256&auto=format&fit=crop'"> -->
      <div class="sidebar-profile-info">
        <!-- <div class="sidebar-profile-name">Administrator</div>
        <div class="sidebar-profile-email">admin@email.com</div> -->
      </div>
    </div>
  </div>
  <!-- ==========================================
         END: Sidebar Component
         ========================================== -->


  <!-- ==========================================
         START: Main Content Area
         ========================================== -->
  <div class="main-wrapper">

    <!-- START: Top Navbar Component -->
    <header class="navbar-custom">
      <div class="navbar-left">
        <!-- Desktop sidebar toggle -->
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
          id="desktop-sidebar-toggle" aria-label="Minimize Sidebar">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <!-- Mobile sidebar toggle -->
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation">
          <i class="bi bi-list"></i>
        </button>

        <!-- Quick Actions Dropdown -->
        <div class="dropdown ms-2">
          <!-- <button class="btn-quick-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
            id="quick-actions-dropdown"> -->
            <!-- <i class="bi bi-plus-lg"></i> -->
            <!-- <span>Create</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-quick-action" aria-labelledby="quick-actions-dropdown">
            <li class="dropdown-header">Quick Action Shortcuts</li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-plus"></i> New Invoice</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-person-plus"></i> New User</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-box-seam"></i> New Product</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> System Settings</a></li>
          </ul> -->
        </div>
      </div>

      <!-- Mid navbar: search pill -->
      <!-- <div class="navbar-search-wrapper">
        <input type="text" class="navbar-search-input" placeholder="Search anything in Spark..." id="main-search">
        <button class="navbar-search-btn" aria-label="Search">
          <i class="bi bi-search"></i>
        </button>
      </div> -->
<br>
      <!-- Right actions -->
      <div class="navbar-actions">
        <!-- Fullscreen Toggle -->
        <!-- <button class="navbar-action-btn me-1" aria-label="Toggle Fullscreen" id="btn-fullscreen">
          <i class="bi bi-arrows-fullscreen"></i>
        </button>
        <div class="dropdown">
          <button class="navbar-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="btn-notifications" data-bs-auto-close="outside">
            <i class="bi bi-bell"></i>
            <span class="navbar-action-badge"></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-notification p-0"
            aria-labelledby="btn-notifications">
            <div class="notification-header">
              <h6 class="notification-title">Notifications</h6>
              <button class="btn-clear-all" type="button">Mark all read</button>
            </div>
            <div class="notification-list">
              <a href="#" class="notification-item">
                <div class="notification-icon bg-success text-white">
                  <i class="bi bi-wallet2"></i>
                </div>
                <div class="notification-content">
                  <p class="notification-text">New sale received: <strong>$150.00</strong></p>
                  <span class="notification-time">2 mins ago</span>
                </div>
                <span class="notification-unread-dot"></span>
              </a>
              <a href="#" class="notification-item">
                <div class="notification-icon bg-primary text-white">
                  <i class="bi bi-person-plus-fill"></i>
                </div>
                <div class="notification-content">
                  <p class="notification-text">New user registered: <strong>John Doe</strong></p>
                  <span class="notification-time">1 hour ago</span>
                </div>
                <span class="notification-unread-dot"></span>
              </a>
            </div>
            <a href="#" class="notification-footer">View All Notifications</a>
          </div>
        </div> -->
        

        <!-- Profile Dropdown -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="profile-dropdown">
            <img src="assets/images/avatar.png" alt="Profile Image" class="navbar-profile-img">
            <span class="navbar-profile-name d-none d-md-inline">Administrator</span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <!-- <li class="dropdown-header">Welcome !</li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> My Account</a></li>
            <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
            <li> -->
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="page-login.php"><i class="bi bi-box-arrow-right"></i>
                Logout</a></li>
          </ul>
        </div>
      </div>
    </header>
    <!-- END: Top Navbar Component -->

    <!-- START: Page Header Banner -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Lihat Pesanan</h1>
        <!-- <p class="page-subtitle">Premium layout elements, toggle switches, and input decorators.</p> -->
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <!-- <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted-green">Home</a></li>
          <li class="breadcrumb-item text-muted-green">Components</li>
          <li class="breadcrumb-item active text-main" aria-current="page">Forms & Input</li> -->
        </ol>
      </nav>
    </div>
    <!-- END: Page Header Banner -->

    <!-- START: Form Component Row Grid Layout -->

          <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Id Pembeli</th>
              <th>Nama Pembeli</th>
              <th>Email</th>
              <th>Alamat</th>
              <!-- <th>Harga Jual</th>
              <th>Stok</th>
              <th>Merk</th> -->
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Row 1 -->
            <tr>
              <td class="table-order-id">#ORD-9982</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_1.jpg" alt="Eleanor Pena" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Eleanor Pena</div>
                    <div class="table-user-sub">eleanor.pena@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Oversized Hoodie</td>
              <td>Apparel</td>
              <td class="table-amount">$89.90</td>
              <!-- <td>Feb 14, 2026</td>
              <td><span class="badge-table success">Paid</span></td> -->
              <!-- <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 2 -->
            <tr>
              <td class="table-order-id">#ORD-9981</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_2.jpg" alt="Wade Warren" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Wade Warren</div>
                    <div class="table-user-sub">wade.warren@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Gaming Console</td>
              <td>Electronics</td>
              <td class="table-amount">$499.00</td>
              <!-- <td>Feb 13, 2026</td>
              <td><span class="badge-table pending">Processing</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 3 -->
            <tr>
              <td class="table-order-id">#ORD-9980</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_3.jpg" alt="Jane Cooper" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Jane Cooper</div>
                    <div class="table-user-sub">jane.cooper@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Premium T-Shirt</td>
              <td>Apparel</td>
              <td class="table-amount">$35.00</td>
              <!-- <td>Feb 12, 2026</td>
              <td><span class="badge-table success">Paid</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 4 -->
            <tr>
              <td class="table-order-id">#ORD-9979</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_4.jpg" alt="Robert Fox" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Robert Fox</div>
                    <div class="table-user-sub">robert.fox@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Wireless Headset</td>
              <td>Accessories</td>
              <td class="table-amount">$129.99</td>
              <!-- <td>Feb 11, 2026</td>
              <td><span class="badge-table failed">Failed</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 5 -->
            <tr>
              <td class="table-order-id">#ORD-9978</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_5.jpg" alt="Albert Flores" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Albert Flores</div>
                    <div class="table-user-sub">albert.flores@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Leather Wallet</td>
              <td>Accessories</td>
              <td class="table-amount">$45.00</td>
              <!-- <td>Feb 10, 2026</td>
              <td><span class="badge-table success">Paid</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 6 -->
            <tr>
              <td class="table-order-id">#ORD-9977</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_6.jpg" alt="Kristin Watson" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Kristin Watson</div>
                    <div class="table-user-sub">kristin.watson@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Ergonomic Chair</td>
              <td>Furniture</td>
              <td class="table-amount">$249.00</td>
              <!-- <td>Feb 09, 2026</td> -->
              <!-- <td><span class="badge-table pending">Processing</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 7 -->
            <tr>
              <td class="table-order-id">#ORD-9976</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_7.jpg" alt="Cody Fisher" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Cody Fisher</div>
                    <div class="table-user-sub">cody.fisher@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Mechanical Keyboard</td>
              <td>Accessories</td>
              <td class="table-amount">$119.00</td>
              <!-- <td>Feb 08, 2026</td>
              <td><span class="badge-table success">Paid</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 8 -->
            <tr>
              <td class="table-order-id">#ORD-9975</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_8.jpg" alt="Bessie Cooper" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Bessie Cooper</div>
                    <div class="table-user-sub">bessie.cooper@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Fitness Tracker</td>
              <td>Electronics</td>
              <td class="table-amount">$79.99</td>
              <!-- <td>Feb 07, 2026</td> -->
              <!-- <td><span class="badge-table failed">Cancelled</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 9 -->
            <tr>
              <td class="table-order-id">#ORD-9974</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_1.jpg" alt="Dianne Russell" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Dianne Russell</div>
                    <div class="table-user-sub">dianne.russell@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Coffee Mug Set</td>
              <td>Home</td>
              <td class="table-amount">$24.50</td>
              <!-- <td>Feb 06, 2026</td>
              <td><span class="badge-table success">Paid</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
            <!-- Row 10 -->
            <tr>
              <td class="table-order-id">#ORD-9973</td>
              <td>
                <div class="table-user-cell">
                  <img src="assets/images/user_2.jpg" alt="Devon Lane" class="table-user-avatar"
                    onerror="this.src='assets/images/avatar.png'">
                  <div>
                    <div class="table-user-name">Devon Lane</div>
                    <div class="table-user-sub">devon.lane@example.com</div>
                  </div>
                </div>
              </td>
              <td class="table-product-name">Desk Organizer</td>
              <td>Office</td>
              <td class="table-amount">$39.99</td>
              <!-- <td>Feb 05, 2026</td>
              <td><span class="badge-table success">Paid</span></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="#" class="table-btn-action" title="View details"><i class="bi bi-eye"></i></a>
                  <a href="#" class="table-btn-action" title="Edit row"><i class="bi bi-pencil"></i></a>
                  <a href="#" class="table-btn-action delete" title="Delete row"><i class="bi bi-trash"></i></a>
                </div>
              </td> -->
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Controls / Pagination -->
      <div class="table-footer-control">
        <span class="table-pagination-info">Showing 1 to 10 of 50 entries</span>
        <nav aria-label="Page navigation">
          <ul class="pagination mb-0 gap-1">
            <li class="page-item disabled"><a class="page-link border-0" href="#"><i class="bi bi-chevron-left"></i></a>
            </li>
            <li class="page-item active"><a class="page-link border-0" href="#">1</a></li>
            <li class="page-item"><a class="page-link border-0" href="#">2</a></li>
            <li class="page-item"><a class="page-link border-0" href="#">3</a></li>
            <li class="page-item"><a class="page-link border-0" href="#"><i class="bi bi-chevron-right"></i></a></li>
          </ul>
        </nav>
      </div>
    </div>
    <!-- <p>Selesai</p> -->

    <!-- <div class="row g-4 mb-4"> -->
<!-- <p>mulai</p> -->
      <!-- Column 1: Basic controls -->
      <!-- <div class="col-12 col-lg-6">
        <div class="card border-light shadow-sm p-4 h-100">
          <h5 class="card-title mb-4">Basic Fields</h5> -->

          <!-- Text input -->
          <!-- <div class="mb-3">
            <label for="basicText" class="form-label-custom">Username</label>
            <input type="text" class="form-control-custom" id="basicText" placeholder="Enter username">
          </div> -->

          <!-- Email input -->
          <!-- <div class="mb-3">
            <label for="basicEmail" class="form-label-custom">Email Address</label>
            <input type="email" class="form-control-custom" id="basicEmail" placeholder="name@example.com">
            <span class="text-muted">We'll never share your email with anyone else.</span>
          </div> -->

          <!-- Password input -->
          <!-- <div class="mb-3">
            <label for="basicPassword" class="form-label-custom">Password</label>
            <input type="password" class="form-control-custom" id="basicPassword"
              placeholder="Enter your secure password">
          </div> -->

          <!-- Disabled State -->
          <!-- <div class="mb-3">
            <label for="basicDisabled" class="form-label-custom">Disabled Input</label>
            <input type="text" class="form-control-custom" id="basicDisabled" value="This input field is disabled"
              disabled>
          </div> -->

          <!-- Readonly State -->
          <!-- <div class="mb-0">
            <label for="basicReadonly" class="form-label-custom">Read-only Input</label>
            <input type="text" class="form-control-custom" id="basicReadonly" value="This field is read-only" readonly>
          </div>
        </div>
      </div> -->

      <!-- Column 2: Selection & Validation -->
      <!-- <div class="col-12 col-lg-6">
        <div class="card border-light shadow-sm p-4 h-100">
          <h5 class="card-title mb-4">Selection & Validation</h5> -->

          <!-- Custom dropdown select -->
          <!-- <div class="mb-3">
            <label for="selectControl" class="form-label-custom">Preferred Theme</label>
            <select class="form-select-custom" id="selectControl">
              <option selected disabled>Choose a visual theme...</option>
              <option value="light">Spark Light Theme</option>
              <option value="dark">Spark Dark Green Theme</option>
              <option value="forest">Forest Breeze Accent</option>
            </select>
          </div> -->

          <!-- Textarea layout -->
          <!-- <div class="mb-3">
            <label for="textareaControl" class="form-label-custom">Description Bio</label>
            <textarea class="form-control-custom" id="textareaControl" rows="3"
              placeholder="Tell us about yourself..."></textarea>
          </div> -->

          <!-- Valid feedback block -->
          <!-- <div class="mb-3">
            <label for="validInput" class="form-label-custom">Valid State Field</label>
            <input type="text" class="form-control-custom is-valid-custom" id="validInput" value="spark_admin_user">
            <div class="form-feedback-custom valid-custom">
              <i class="bi bi-check-circle-fill"></i> Username is available!
            </div>
          </div> -->

          <!-- Invalid feedback block -->
          <!-- <div class="mb-0">
            <label for="invalidInput" class="form-label-custom">Invalid State Field</label>
            <input type="email" class="form-control-custom is-invalid-custom" id="invalidInput"
              value="invalid-email-format">
            <div class="form-feedback-custom invalid-custom">
              <i class="bi bi-exclamation-circle-fill"></i> Please enter a valid email address.
            </div>
          </div>
        </div>
      </div> -->

      <!-- Column 3: Sizing & Groups -->
      <!-- <div class="col-12 col-lg-6">
        <div class="card border-light shadow-sm p-4 h-100">
          <h5 class="card-title mb-4">Input Sizing & Decorator Groups</h5> -->

          <!-- Sizing examples -->
          <!-- <div class="mb-4">
            <div class="mb-2">
              <label class="form-label-custom">Large Control</label>
              <input type="text" class="form-control-custom form-control-custom-lg" placeholder="Large text box input">
            </div>
            <div class="mb-2">
              <label class="form-label-custom">Default Control</label>
              <input type="text" class="form-control-custom" placeholder="Default text box input">
            </div>
            <div>
              <label class="form-label-custom">Small Control</label>
              <input type="text" class="form-control-custom form-control-custom-sm" placeholder="Small text box input">
            </div>
          </div>

          <h6 class="mb-3">Input Groups</h6> -->

          <!-- Prepend group -->
          <!-- <div class="mb-3">
            <div class="input-group-custom">
              <span class="input-group-text-custom">@</span>
              <input type="text" class="form-control-custom" placeholder="Username">
            </div>
          </div> -->

          <!-- Append group -->
          <!-- <div class="mb-3">
            <div class="input-group-custom">
              <input type="text" class="form-control-custom" placeholder="Recipient username">
              <span class="input-group-text-custom">@sparkadmin.web.id</span>
            </div>
          </div> -->

          <!-- Prepend + Append Icon group -->
          <!-- <div class="mb-0">
            <div class="input-group-custom">
              <span class="input-group-text-custom"><i class="bi bi-wallet2"></i></span>
              <input type="text" class="form-control-custom" placeholder="Billing Amount">
              <span class="input-group-text-custom">USD</span>
            </div>
          </div>
        </div>
      </div> -->

      <!-- Column 4: Checkboxes, Radios & iOS Switches -->
      <!-- <div class="col-12 col-lg-6">
        <div class="card border-light shadow-sm p-4 h-100">
          <h5 class="card-title mb-4">Checkboxes, Radios & Switches</h5>

          <h6 class="mb-3">Custom Checkboxes</h6>
          <div class="mb-4">
            <div class="form-check-custom">
              <input class="form-check-input-custom" type="checkbox" id="checkActive" checked>
              <label class="form-check-label" for="checkActive">Checked custom option</label>
            </div> -->
            <!-- <div class="form-check-custom">
              <input class="form-check-input-custom" type="checkbox" id="checkInactive">
              <label class="form-check-label" for="checkInactive">Unchecked custom option</label>
            </div>
            <div class="form-check-custom">
              <input class="form-check-input-custom" type="checkbox" id="checkDisabled" disabled checked>
              <label class="form-check-label" for="checkDisabled">Disabled checked option</label>
            </div>
          </div> -->

          <!-- <h6 class="mb-3">Custom Radio Elements</h6>
          <div class="mb-4">
            <div class="form-check-custom">
              <input class="form-check-input-custom" type="radio" name="radioGroup" id="radioOne" checked>
              <label class="form-check-label" for="radioOne">Radio option selection A</label>
            </div>
            <div class="form-check-custom">
              <input class="form-check-input-custom" type="radio" name="radioGroup" id="radioTwo">
              <label class="form-check-label" for="radioTwo">Radio option selection B</label>
            </div>
          </div> -->

          <!-- <h6 class="mb-3">Premium Switch Controls (iOS Style)</h6>
          <div>
            <div class="form-switch-custom">
              <input class="form-switch-input-custom" type="checkbox" id="switchOne" checked>
              <label class="form-switch-label" for="switchOne">Enable email notifications</label>
            </div>
            <div class="form-switch-custom">
              <input class="form-switch-input-custom" type="checkbox" id="switchTwo">
              <label class="form-switch-label" for="switchTwo">Keep profile visibility public</label>
            </div>
            <div class="form-switch-custom">
              <input class="form-switch-input-custom" type="checkbox" id="switchThree" disabled>
              <label class="form-switch-label" for="switchThree">Enable background synchronization (Disabled)</label>
            </div>
          </div>
        </div>
      </div> -->
<!-- <p>sampai iki</p> -->
    <!-- </div> -->
    <!-- END: Form Component Row Grid Layout -->

    <!-- START: Footer Component -->
    <footer class="footer-custom">
      <div class="footer-left">
        <span class="footer-logo">
          <i class="bi bi-asterisk"></i> Spark Admin
        </span>
        <span class="footer-separator">|</span>
        <span class="footer-copy">&copy; 2026 Made with <i class="bi bi-heart-fill text-danger footer-heart"></i> by<a
            href="https://sparkadminpro.gumroad.com/" target="_blank">Spark Admin</a>• Distributed by <a
            href="https://www.themewagon.com/" target="_blank">ThemeWagon</a> </span>
      </div>
      <div class="footer-right">
        <ul class="footer-links">
          <li><a href="#" class="footer-link">Overview</a></li>
          <li><a href="#" class="footer-link">Statistics</a></li>
          <li><a href="#" class="footer-link">Help & Documentation</a></li>
          <li><a href="#" class="footer-link">Status <span class="status-dot"></span></a></li>
        </ul>
      </div>
    </footer>
    <!-- END: Footer Component -->

  </div>
  <!-- ==========================================
         END: Main Content Area
         ========================================== -->

  <!-- Local Third-Party Libraries Script dependencies -->
  <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
  <script src="assets/libs/flatpickr/flatpickr.min.js"></script>

  <!-- Local dashboard interactions controller -->
  <script src="assets/js/dashboard.js"></script>
</body>

</html>