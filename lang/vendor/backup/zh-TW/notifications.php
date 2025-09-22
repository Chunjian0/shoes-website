<?php

return [
    'exception_message' => 'Exception message: :message',
    'exception_trace' => 'Exception tracking: :trace',
    'exception_message_title' => 'Exception message',
    'exception_trace_title' => 'Exception tracking',

    'backup_failed_subject' => ':application_name Backup failed',
    'backup_failed_body' => 'Important Note: Backup :application_name An error occurred while',

    'backup_successful_subject' => ':application_name Successful backup',
    'backup_successful_subject_title' => 'The backup was successful!',
    'backup_successful_body' => 'Good news, :application_name Backup successfully, located on disk :disk_name middle.',

    'cleanup_failed_subject' => 'Clear :application_name Backup failed.',
    'cleanup_failed_body' => 'Clear backup :application_name An error occurred while',

    'cleanup_successful_subject' => 'Cleared successfully :application_name Backup of',
    'cleanup_successful_subject_title' => 'Clear the backup successfully!',
    'cleanup_successful_body' => 'Cleared successfully :disk_name On disk :application_name Backup of.',

    'healthy_backup_found_subject' => ':disk_name On disk :application_name The backup is healthy',
    'healthy_backup_found_subject_title' => ':application_name The backup is healthy',
    'healthy_backup_found_body' => ':application_name The backup is healthy.Good job!',

    'unhealthy_backup_found_subject' => 'Important Note::application_name The backup is unhealthy',
    'unhealthy_backup_found_subject_title' => 'Important Note::application_name Backup is unhealthy. :problem',
    'unhealthy_backup_found_body' => ':disk_name On disk :application_name The backup is unhealthy.',
    'unhealthy_backup_found_not_reachable' => 'Unable to access the backup target. :error',
    'unhealthy_backup_found_empty' => 'There is no backup of this application at all.',
    'unhealthy_backup_found_old' => 'The most recent backup was created in :date , too old.',
    'unhealthy_backup_found_unknown' => 'Sorry, the exact reason cannot be determined.',
    'unhealthy_backup_found_full' => 'Backup takes up too much storage space.Currently occupied :disk_usage , higher than allowed limit :disk_limit.',

    'no_backups_info' => 'No backups have been made yet',
    'application_name' => 'Application name',
    'backup_name' => 'Backup name',
    'disk' => 'Disk',
    'newest_backup_size' => 'Latest backup size',
    'number_of_backups' => 'Number of backups',
    'total_storage_used' => 'Total storage used',
    'newest_backup_date' => 'Latest backup size',
    'oldest_backup_date' => 'The earliest backup size',
];
