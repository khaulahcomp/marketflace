<?php
if (!is_post()) redirect('');
csrf_verify();
logout_user();
flash('success', 'Anda sudah keluar.');
redirect('');
