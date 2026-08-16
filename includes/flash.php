<?php
function set_flash_message($type, $message)
{
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function display_flash_messages()
{
    if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $flash) {
            $typeClass = $flash['type'] === 'error' ? 'alert-danger' : 'alert-success';
            echo '<div class="alert ' . $typeClass . '">' . h($flash['message']) . '</div>';
        }
        unset($_SESSION['flash_messages']);
    }
}
