<div class="form-sync-for-notion-header">
        <div class="form-sync-for-notion-header-inner">
            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../../assets/notion-forms-logo.png'); ?>" alt="Form Sync for Notion Logo" class="form-sync-for-notion-logo">
            <h1 class="form-sync-for-notion-title">Form Sync for Notion</h1>
            <nav class="form-sync-for-notion-nav">
    <?php

        $screen = get_current_screen();
        switch ($screen->id) {
        case 'toplevel_page_form-sync-for-notion':
                $current_page = 'form-sync-for-notion';
                break;
        case 'form-sync-for-notion_page_form-sync-for-notion-confirmation':
                $current_page = 'form-sync-for-notion-confirmation';
                break;
        case 'form-sync-for-notion_page_form-sync-for-notion-styles':
                $current_page = 'form-sync-for-notion-styles';
                break;
        case 'form-sync-for-notion_page_form-sync-for-notion-settings':
                $current_page = 'form-sync-for-notion-settings';
                break;
        case 'form-sync-for-notion_page_form-sync-for-notion-documentation':
                $current_page = 'form-sync-for-notion-documentation';
                break;
        }

    ?>

    <?php if (form_sync_for_notion_is_setup()) : ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=form-sync-for-notion')); ?>" class="<?php echo $current_page === 'form-sync-for-notion' ? 'active' : ''; ?>">Notion Form</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=form-sync-for-notion-confirmation')); ?>" class="<?php echo $current_page === 'form-sync-for-notion-confirmation' ? 'active' : ''; ?>">Confirmation Page</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=form-sync-for-notion-styles')); ?>" class="<?php echo $current_page === 'form-sync-for-notion-styles' ? 'active' : ''; ?>">Styles</a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=form-sync-for-notion-settings')); ?>" class="<?php echo $current_page === 'form-sync-for-notion-settings' ? 'active' : ''; ?>">Settings</a>

    <?php endif; ?>

            <a href="<?php echo esc_url(admin_url('admin.php?page=form-sync-for-notion-documentation')); ?>" class="<?php echo $current_page === 'form-sync-for-notion-documentation' ? 'active' : ''; ?>">Documentation</a>


                </nav>
     
        </div>
    </div>