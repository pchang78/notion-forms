<div class="notion-forms-header">
        <div class="notion-forms-header-inner">
            <img src="<?php echo plugin_dir_url(__FILE__) . '../../assets/notion-forms-logo.png'; ?>" alt="Notion Content Logo" class="notion-forms-logo">
            <h1 class="notion-forms-title">Notion Forms</h1>
            <nav class="notion-forms-nav">
    <?php
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';
    ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms')); ?>" class="<?php echo $current_page === 'notion-forms' ? 'active' : ''; ?>">Notion Form</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-confirmation')); ?>" class="<?php echo $current_page === 'notion-forms-confirmation' ? 'active' : ''; ?>">Confirmation Page</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-styles')); ?>" class="<?php echo $current_page === 'notion-forms-styles' ? 'active' : ''; ?>">Styles</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-settings')); ?>" class="<?php echo $current_page === 'notion-forms-settings' ? 'active' : ''; ?>">Settings</a>


                </nav>
     
        </div>
    </div>