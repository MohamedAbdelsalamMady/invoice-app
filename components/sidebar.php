<div class="sidebar" data-background-color="dark">
      <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
          <a href="index.php" class="logo">
            <h3 style="color:antiquewhite">Invoices</h3>
          </a>
          <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
              <i class="gg-menu-right"></i>
            </button>
            <button class="btn btn-toggle sidenav-toggler">
              <i class="gg-menu-left"></i>
            </button>
          </div>
          <button class="topbar-toggler more">
            <i class="gg-more-vertical-alt"></i>
          </button>
        </div>
        <!-- End Logo Header -->
      </div>
      <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
          <ul class="nav nav-secondary">
            <li class="nav-item active">
              <a data-bs-toggle="collapse" href="#dashboard" class="collapsed" aria-expanded="false">
                <i class="fas fa-home"></i>
                <p>Dashboard</p>

              </a>

            </li>
            <li class="nav-section">
              <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
              </span>
              <h4 class="text-section">Components</h4>
            </li>
            <li class="nav-item">
              <a data-bs-toggle="collapse" href="#base">
                <i class="fas fa-layer-group"></i>
                <p>Invoices</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="base">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="show_invoice.php">
                      <span class="sub-item">Show Invoices</span>
                    </a>
                  </li>
                  <li>
                    <a href="add_invoice.php">
                      <span class="sub-item">Add Invoices</span>
                    </a>
                  </li>
                </ul>
              </div>
            </li>

            <li class="nav-item">
              <a data-bs-toggle="collapse" href="#forms">
                <i class="fas fa-pen-square"></i>
                <p>Clients</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="forms">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="show_clients.php">
                      <span class="sub-item">Show Clients</span>
                    </a>
                  </li>
                  <li>
                    <a href="add_clients.php">
                      <span class="sub-item">Add Clients</span>
                    </a>
                  </li>

                </ul>
              </div>
            </li>


            <li class="nav-item">
              <a data-bs-toggle="collapse" href="#lists">
                <i class="fas fa-pen-square"></i>
                <p>List Of Items</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="lists">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="show_listOfItems.php">
                      <span class="sub-item">Show List of Items</span>
                    </a>
                  </li>
                  <li>
                    <a href="add_listOfItems.php">
                      <span class="sub-item">Add List of Items</span>
                    </a>
                  </li>

                </ul>
              </div>
            </li>


            <li class="nav-item">
              <a data-bs-toggle="collapse" href="#users">
                <i class="fas fa-pen-square"></i>
                <p>Users</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="users">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="show_user.php">
                      <span class="sub-item">Show Users</span>
                    </a>
                  </li>

                </ul>
              </div>
            </li>

          </ul>
        </div>
      </div>
    </div>