<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel · Global Car Metepec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-left">
                <i class="fas fa-crown"></i>
                <h1>Global Car <span>Metepec</span></h1>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></span>
                </div>
                <a href="?action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo</a>
                <a href="?action=logout" class="btn btn-outline btn-sm"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php
            if ($_GET['msg'] == 'added') echo 'Vehículo agregado.';
            elseif ($_GET['msg'] == 'updated') echo 'Vehículo actualizado.';
            elseif ($_GET['msg'] == 'deleted') echo 'Vehículo eliminado.';
            ?>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <h2 style="color:var(--gold); margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-car"></i> Listado de Vehículos
            </h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): 
                        $img = $v['image_base'] ? 'assets/images/'.$v['image_base'].'1'.$v['image_extension'] : 'assets/images/placeholder.jpg';
                    ?>
                    <tr>
                        <td>#<?php echo $v['id']; ?></td>
                        <td><img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($v['brand'].' '.$v['model']); ?>" class="vehicle-image"></td>
                        <td><?php echo htmlspecialchars($v['brand']); ?></td>
                        <td><?php echo htmlspecialchars($v['model']); ?></td>
                        <td><?php echo $v['year']; ?></td>
                        <td>$<?php echo number_format($v['price'],2); ?> MDP</td>
                        <td class="action-buttons">
                            <a href="?action=edit&id=<?php echo $v['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Editar</a>
                            <a href="?action=delete&id=<?php echo $v['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este vehículo?')"><i class="fas fa-trash"></i> Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div>© 2026 Global Car Metepec · Todos los derechos reservados</div>
            <div><a href="assets/docs/aviso-privacidad.pdf" target="_blank"><i class="fas fa-file-pdf"></i> Aviso de Privacidad</a></div>
        </div>
    </div>
</body>
</html>