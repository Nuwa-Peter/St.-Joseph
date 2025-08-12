import os
from playwright.sync_api import sync_playwright, expect

def run_verification():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        try:
            base_url = os.environ.get("BASE_URL", "http://localhost:8000")

            # Navigate to the login page
            page.goto(f"{base_url}/login.php")

            # Fill in the login form
            page.get_by_label("Email").fill("root@school.app")
            page.get_by_label("Password").fill("password")

            # Click the login button
            page.get_by_role("button", name="Login").click()

            # Wait for navigation to the dashboard and check for a known element
            expect(page).to_have_url(f"{base_url}/dashboard.php")
            expect(page.get_by_role("heading", name="Dashboard")).to_be_visible()

            # Take a screenshot of the page
            screenshot_path = "jules-scratch/verification/sidebar_verification.png"
            page.screenshot(path=screenshot_path)
            print(f"Screenshot saved to {screenshot_path}")

        except Exception as e:
            print(f"An error occurred: {e}")
            page.screenshot(path="jules-scratch/verification/error.png")

        finally:
            browser.close()

if __name__ == "__main__":
    run_verification()
