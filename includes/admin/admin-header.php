<div class="notion-forms-header">
        <div class="notion-forms-header-inner">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../../assets/notion-forms-logo.png'); ?>" alt="Notion Content Logo" class="notion-forms-logo">
            <h1 class="notion-forms-title">Notion Forms</h1>
            <nav class="notion-forms-nav">
    <?php

        $screen = get_current_screen();
        switch ($screen->id) {
        case 'toplevel_page_notion-forms':
                $current_page = 'notion-forms';
                break;
        case 'notion-forms_page_notion-forms-confirmation':
                $current_page = 'notion-forms-confirmation';
                break;
        case 'notion-forms_page_notion-forms-styles':
                $current_page = 'notion-forms-styles';
                break;
        case 'notion-forms_page_notion-forms-settings':
                $current_page = 'notion-forms-settings';
                break;
        case 'notion-forms_page_notion-forms-documentation':
                $current_page = 'notion-forms-documentation';
                break;
        }

    ?>

    <?php if (notion_forms_is_setup()) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms')); ?>" class="<?php echo $current_page === 'notion-forms' ? 'active' : ''; ?>">Notion Form</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-confirmation')); ?>" class="<?php echo $current_page === 'notion-forms-confirmation' ? 'active' : ''; ?>">Confirmation Page</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-styles')); ?>" class="<?php echo $current_page === 'notion-forms-styles' ? 'active' : ''; ?>">Styles</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-settings')); ?>" class="<?php echo $current_page === 'notion-forms-settings' ? 'active' : ''; ?>">Settings</a>

    <?php endif; ?>

            <a href="<?php echo esc_url(admin_url('admin.php?page=notion-forms-documentation')); ?>" class="<?php echo $current_page === 'notion-forms-documentation' ? 'active' : ''; ?>">Documentation</a>


                </nav>
     
        </div>
    </div>