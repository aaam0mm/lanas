import time
import logging
import os
import sys
from MyClasses.CloudflareBypasser import CloudflareBypasser
from DrissionPage import ChromiumPage, ChromiumOptions
import fitz
from pdf2image import convert_from_path
import pytesseract
import tempfile

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.StreamHandler(),
        logging.FileHandler('/Applications/MAMP/htdocs/nas/py/cloudflare_bypass.log', mode='w')
    ]
)

def get_chromium_options(browser_path: str, arguments: list, image_stat=True) -> ChromiumOptions:
    """
    Configures and returns Chromium options.
    :param browser_path: Path to the Chromium browser executable.
    :param arguments: List of arguments for the Chromium browser.
    :return: Configured ChromiumOptions instance.
    """
    options = ChromiumOptions()
    if image_stat:
        options.no_imgs(True).mute(True)
    options.set_paths(browser_path=browser_path)
    for argument in arguments:
        options.set_argument(argument)
    return options



def init_driver(use_private_mode=False):
    """
    Initializes the Chromium driver. If use_private_mode is True, the driver will start in private/incognito mode.

    :param use_private_mode: Boolean flag to open browser in private/incognito mode.
    :return: Chromium driver instance.
    """
    # Chromium Browser Path
    isHeadless = os.getenv('HEADLESS', 'false').lower() == 'true'
    # isHeadless = headless == 'on'
    
    if isHeadless:
        from pyvirtualdisplay import Display
        display = Display(visible=0, size=(1920, 1080))
        display.start()

    browser_path = os.getenv('CHROME_PATH', "/usr/bin/google-chrome")

    # Temporary directory for user data to simulate a new browser session
    temp_dir = tempfile.mkdtemp()

    # Arguments to make the browser better for automation and less detectable
    arguments = [
        "-no-first-run",
        "-force-color-profile=srgb",
        "-metrics-recording-only",
        "-password-store=basic",
        "-use-mock-keychain",
        "-export-tagged-pdf",
        "-no-default-browser-check",
        "-disable-background-mode",
        "-enable-features=NetworkService,NetworkServiceInProcess,LoadCryptoTokenExtension,PermuteTLSExtensions",
        "-disable-features=FlashDeprecationWarning,EnablePasswordsAccountStorage",
        "-deny-permission-prompts",
        "-disable-gpu",
        "-accept-lang=en-US",
        f"--user-data-dir={temp_dir}",  # Set the user data directory to the temp folder
    ]

    # Add incognito mode if the flag is set
    if use_private_mode:
        arguments.append("--incognito")  # Start in incognito mode to avoid saving data

    if isHeadless:
        arguments.append("--headless")

    options = get_chromium_options(browser_path, arguments)

    # Initialize the browser
    driver = ChromiumPage(addr_or_opts=options)

    try:
        # Where the bypass starts
        # logging.info('Starting Cloudflare bypass.')
        cf_bypasser = CloudflareBypasser(driver)
        cf_bypasser.bypass()

        return driver

    except Exception as e:
        # logging.error("An error occurred during driver initialization: %s", str(e))
        logging.error("")
        return None

def login_gmail(driver):
    email = sys.argv[4] if sys.argv[4] else 'salahbellal394@gmail.com'
    password = sys.argv[5] if sys.argv[5] else '(oQl3dkbG3%BYdRQ5K'
    response = False
    try:
        login_btn = driver.ele('css:div.gap-2:nth-child(1) > button:nth-child(1)', timeout=15)
        if login_btn:
            login_btn.click()
            try:
                # Where the bypass starts
                gmail_btn = driver.ele('xpath:/html/body/div[1]/main/section/div[2]/div[3]/button[1]', timeout=15)
                if gmail_btn:
                    # driver.run_js("arguments[0].click();", gmail_btn)
                    gmail_btn.click()
                    try:
                        input_email = driver.ele('xpath://*[@id="identifierId"]', timeout=15)

                        if input_email:
                            time.sleep(1)
                            input_email.input(email)
                            try:
                                cnt_btn = driver.ele('xpath:/html/body/div[1]/div[1]/div[2]/c-wiz/div/div[3]/div/div[1]/div/div/button', timeout=15)
                                if cnt_btn:
                                    cnt_btn.click()
                                    try:
                                        input_password = driver.ele('css:div#password > div:nth-child(1) > div:nth-child(1) > div:nth-child(1) > input:nth-child(1)', timeout=15)

                                        if input_password:
                                            time.sleep(1)
                                            input_password.input(password)

                                            try:
                                                cnt_btn_2 = driver.ele('css:button.VfPpkd-LgbsSe-OWXEXe-k8QpJ', timeout=15)

                                                if cnt_btn_2:
                                                    cnt_btn_2.click()
                                                    response = True

                                            except Exception as e:
                                                response = False


                                    except Exception as e:
                                        response = False

                            except Exception as e:
                                response = False

                    except Exception as e:
                        response = False

            except Exception as e:
                response = False
        else:
            response = False
    except Exception as e:
        response = False
    return response

def check_loged_in(driver):
    try:
        user_img = driver.ele(
            'css:div > img[alt="User"]',
            7
        )
        if user_img:
            return True
        else:
            return False
    except Exception as e:
        logging.error("Error checking login status: %s", str(e))
        return False

def book_summary(driver):
    driver.get('https://chatgpt.com')
    logged_in = check_loged_in(driver)
    login_gmail_stat = True
    if logged_in == False:
        login_gmail_stat = login_gmail(driver)
    if login_gmail_stat == True:
        try:
            upload_stat = upload_pdf_with_text(driver)
            if upload_stat == True:
                time.sleep(25)
                msg = driver.ele('css:article[data-testid="conversation-turn-3"] div[data-message-model-slug]', timeout=5)
                # if msg:
                #     print(f"{msg.text}")
                if msg:
                    # Extract innerHTML instead of plain text
                    styled_content = driver.run_js("return arguments[0].innerHTML;", msg)
                    print(styled_content)
                else:
                    print("false")
            else:
                # return False
                print("false")


        except Exception as e:
            # logging.error("Textarea error occurred: %s", str(e))
            print("false")
        finally:
            driver.quit()
    else:
        print("false")
        driver.quit()

def upload_pdf_with_text(driver):
    # Retrieve arguments
    book_title = sys.argv[1] if sys.argv[1] else "Unknown Title"  # Default if empty
    book_author = sys.argv[2] if sys.argv[2] else "Unknown Author"  # Default if empty
    books_lang = sys.argv[3] if sys.argv[3] else "Unknown Lang"  # Default if empty
    init_text = sys.argv[6] if sys.argv[6] else f"اريد تلخيص كامل ومفهوم للكتاب الذي عنوانه '{book_title}' و كاتبه هو '{book_author}'  باللغة '{books_lang}' وان اخطأت في بعض المعلومات يرجى ان تصححها"
    # Remaining arguments are file paths
    books_paths = sys.argv[7:] if len(sys.argv) > 7 else []
    continue_stat = False
    try:
        textarea = driver.ele('css:#composer-background div > textarea.block', timeout=15)
        if textarea:
            text = init_text
            text = text.replace("{t}", f"'{book_title}'")
            text = text.replace("{a}", f"'{book_author}'")
            text = text.replace("{l}", f"'{books_lang}'")
            check_upload_stat = driver.ele('css:#composer-background div.h-8 > button[data-state="closed"].rounded-bl-xl', timeout=5)
            if not check_upload_stat:
                try:
                    file_input = driver.ele('css:input[type="file"].hidden', timeout=15)
                    if file_input:
                        if len(books_paths) > 0:
                            pdf_file_path = "\n".join(books_paths) if len(books_paths) > 1 else books_paths[0]

                            file_input.input(pdf_file_path)
                            time.sleep(1)
                            circle = driver.ele('css:div > svg > circle', timeout=10)
                            if circle:
                                # logging.info("Upload progress started.")
                                # print("Upload progress started.")
                                upload_complete = wait_for_upload_to_complete(driver, timeout=30)
                                if upload_complete:
                                    text += f"\n الكتاب مرفق"
                                    continue_stat = True
                                else:
                                    continue_stat = False

                            else:
                                continue_stat = False
                        else:
                            continue_stat = True
                    else:
                        continue_stat = False
                except Exception as e:
                    # logging.error("An error occurred: %s", str(e))
                    logging.error("")
                    continue_stat = False
            else:
                continue_stat = True

            if continue_stat:
                textarea.input(text)
                time.sleep(1)
                btn = driver.ele('css:#composer-background > div.flex.items-center.justify-between > button', timeout=15)
                if btn:
                    btn.click()
                    time.sleep(1)
                    return True
            else:
                return False

    except Exception as e:
        # logging.error("An error occurred: %s", str(e))
        return False

def wait_for_upload_to_complete(driver, timeout=20, interval=0.5):
    """
    Waits for the upload to complete by checking if the loading circle disappears.
    Args:
        driver (ChromiumPage): The DrissionPage driver instance.
        timeout (int): Maximum time to wait for the circle to disappear.
        interval (float): Time in seconds to wait between checks.
    Returns:
        bool: True if the upload completed, False if timed out.
    """
    start_time = time.time()
    selector = 'css:div > svg > circle'

    while time.time() - start_time < timeout:
        # Check if the circle element exists
        if not driver.ele(selector):
            return True  # Upload complete (circle no longer exists)
        time.sleep(interval)  # Wait before rechecking

    return False  # Timeout reached

def wait_for_element(driver, js_code, timeout=10, interval=0.5):
    """
    Waits for an element to appear by executing JavaScript repeatedly.
    :param driver: The DrissionPage driver instance.
    :param js_code: JavaScript code to find the element.
    :param timeout: Maximum time to wait (in seconds).
    :param interval: Time between retries (in seconds).
    :return: The element if found, otherwise None.
    """
    end_time = time.time() + timeout
    while time.time() < end_time:
        element = driver.run_js(js_code)
        if element:
            return element
        time.sleep(interval)  # Wait before retrying
    return None

def main():

    os.environ['PATH'] = os.environ['PATH'] + ':/opt/X11/bin'

    if len(sys.argv) < 7:  # Minimum required: title, author, count
        print("Error: Not enough arguments provided!")
        sys.exit(1)

    driver = init_driver(use_private_mode=True)

    book_summary(driver)


if __name__ == '__main__':
    main()
