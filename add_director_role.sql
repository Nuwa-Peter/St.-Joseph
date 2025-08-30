-- Add Director role to the school management system
-- This script creates a Director user with full system access

-- Insert a new Director user
INSERT INTO `users` (
    `first_name`, 
    `last_name`, 
    `role`, 
    `gender`, 
    `email`, 
    `password`, 
    `status`, 
    `created_at`, 
    `updated_at`, 
    `student_type`, 
    `username`
) VALUES (
    'School', 
    'Director', 
    'director', 
    'Male', 
    'director@school.app', 
    '$2y$12$HdTw146Kv75LA2QWxjaAdexvi3N2t0jM85r5sp7nz2F1/jod6OQMu', -- password: password123
    'active', 
    NOW(), 
    NOW(), 
    'day', 
    'director'
);

-- Display the created user
SELECT 
    id,
    first_name,
    last_name,
    role,
    email,
    username,
    status,
    created_at
FROM users 
WHERE role = 'director' 
ORDER BY created_at DESC;
