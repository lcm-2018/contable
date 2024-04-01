<?php
session_start();
$estado = $_POST['fs'];
if ($estado = '1') {
    echo '  <a type="button" class="nav-link sombra">
                <i class="fas fa-compress-arrows-alt" style="color: #C39BD3;"></i>
            </a>';
} else {
    echo '  <a type="button" class="nav-link sombra">
                <i class="fas fa-expand-arrows-alt" style="color: #C39BD3;"></i>
            </a>';
}
