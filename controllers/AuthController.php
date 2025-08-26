<?php

require_once __DIR__ . '/../includes/view.php';

class AuthController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        require_once __DIR__ . '/../config.php';

        $data = [
            'login_identifier' => '',
            'password' => '',
            'login_err' => '',
            'password_err' => ''
        ];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (empty(trim($_POST["login_identifier"]))) {
                $data['login_err'] = "Please enter username or email.";
            } else {
                $data['login_identifier'] = trim($_POST["login_identifier"]);
            }

            if (empty(trim($_POST["password"]))) {
                $data['password_err'] = "Please enter your password.";
            } else {
                $data['password'] = trim($_POST["password"]);
            }

            if (empty($data['login_err']) && empty($data['password_err'])) {
                $sql = "SELECT id, username, first_name, last_name, email, password, role FROM users WHERE username = ? OR email = ?";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ss", $data['login_identifier'], $data['login_identifier']);

                    if ($stmt->execute()) {
                        $stmt->store_result();

                        if ($stmt->num_rows == 1) {
                            $stmt->bind_result($id, $username, $first_name, $last_name, $email, $hashed_password, $role);
                            if ($stmt->fetch()) {
                                if (password_verify($data['password'], $hashed_password)) {
                                    $name = $first_name . ' ' . $last_name;
                                    $initials = '';
                                    $parts = explode(' ', $name);
                                    foreach ($parts as $part) {
                                        $initials .= strtoupper(substr($part, 0, 1));
                                    }

                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["id"] = $id;
                                    $_SESSION["username"] = $username;
                                    $_SESSION["name"] = $name;
                                    $_SESSION["initials"] = $initials;
                                    $_SESSION["role"] = $role;

                                    // Role-based redirection
                                    if ($role === 'parent') {
                                        // --- Parent Login Notification ---
                                        $admin_roles_to_notify = ['root', 'director', 'headteacher'];
                                        $admin_ids = [];
                                        $sql_admins = "SELECT id FROM users WHERE role IN ('" . implode("','", $admin_roles_to_notify) . "')";
                                        $result_admins = $conn->query($sql_admins);
                                        while($row = $result_admins->fetch_assoc()) {
                                            $admin_ids[] = $row['id'];
                                        }

                                        if(!empty($admin_ids)) {
                                            $notification_message = "Parent " . $name . " has logged in.";
                                            $notification_link = "profile.php?id=" . $id;
                                            $notify_sql = "INSERT INTO app_notifications (user_id, message, link) VALUES (?, ?, ?)";
                                            $notify_stmt = $conn->prepare($notify_sql);
                                            foreach($admin_ids as $admin_id) {
                                                $notify_stmt->bind_param("iss", $admin_id, $notification_message, $notification_link);
                                                $notify_stmt->execute();
                                            }
                                            $notify_stmt->close();
                                        }
                                        // --- End Notification ---

                                        header("location: parent_dashboard.php");
                                    } else {
                                        header("location: dashboard.php");
                                    }
                                    exit;
                                } else {
                                    $data['login_err'] = "Invalid username/email or password.";
                                }
                            }
                        } else {
                            $data['login_err'] = "Invalid username/email or password.";
                        }
                    } else {
                        $data['login_err'] = "Oops! Something went wrong. Please try again later.";
                    }
                    $stmt->close();
                }
            }
            $conn->close();
        }

        // Load the login view
        view('login', $data);
    }
}
