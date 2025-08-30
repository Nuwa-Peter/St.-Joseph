from playwright.sync_api import sync_playwright, expect

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # Go to login.php, which should now redirect to the dashboard
        page.goto("http://localhost:8000/login.php")

        # Wait for the welcome message to be visible
        welcome_message = page.locator("h4")
        expect(welcome_message).to_contain_text("Welcome, Super Admin!")

        # The sidebar is inside a div with class 'sidebar'.
        sidebar = page.locator("div.sidebar")

        # Take a screenshot of the sidebar.
        sidebar.screenshot(path="jules-scratch/verification/sidebar_verification.png")

        browser.close()

run()
