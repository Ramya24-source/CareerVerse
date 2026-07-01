"""Utility functions for webdriver setup and management"""
import os
import sys
import platform
import subprocess
import streamlit as st
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options

# Try to import webdriver managers
try:
    from webdriver_manager.chrome import ChromeDriverManager
    webdriver_manager_available = True
except ImportError:
    webdriver_manager_available = False

try:
    import chromedriver_autoinstaller
    autoinstaller_available = True
except ImportError:
    autoinstaller_available = False


def get_chrome_version():
    """Get installed Chrome/Chromium version"""
    system = platform.system()

    if system == "Windows":
        chrome_paths = [
            r"C:\Program Files\Google\Chrome\Application\chrome.exe",
            r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
            os.path.expandvars(r"%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe")
        ]
        for path in chrome_paths:
            if os.path.exists(path):
                try:
                    # Escape backslashes for WMIC
                    escaped_path = path.replace("\\", "\\\\")
                    output = subprocess.check_output(
                        ['wmic', 'datafile', 'where', f'name="{escaped_path}"', 'get', 'Version', '/value'],
                        stderr=subprocess.STDOUT
                    )
                    version_str = output.decode('utf-8').strip()
                    if "Version=" in version_str:
                        return version_str.split('=')[1].split('.')[0]
                except Exception:
                    # Fallback: try --version
                    try:
                        output = subprocess.check_output([path, '--version'], stderr=subprocess.STDOUT)
                        return output.decode('utf-8').strip().split()[-1].split('.')[0]
                    except Exception:
                        continue

    else:
        # Linux / Mac
        for binary in ['/usr/bin/google-chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser']:
            if os.path.exists(binary):
                try:
                    output = subprocess.check_output([binary, '--version'], stderr=subprocess.STDOUT)
                    return output.decode('utf-8').strip().split()[-1].split('.')[0]
                except Exception:
                    continue

    return "120"  # default if detection fails


def run_setup_script():
    """Run setup_chromedriver.py script to install chromedriver"""
    try:
        script_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        setup_script = os.path.join(script_dir, "setup_chromedriver.py")
        if os.path.exists(setup_script):
            st.info("Running chromedriver setup script...")
            result = subprocess.run([sys.executable, setup_script], capture_output=True, text=True)
            if result.returncode == 0:
                st.success("Chromedriver setup completed successfully!")
                for line in result.stdout.splitlines():
                    if "Chromedriver path:" in line:
                        return line.split("Chromedriver path:")[1].strip()
            else:
                st.warning(f"Chromedriver setup failed: {result.stderr}")
        else:
            st.warning(f"Setup script not found at {setup_script}")
    except Exception as e:
        st.warning(f"Error running setup script: {str(e)}")

    return None


def get_chromedriver_path():
    """Return existing chromedriver path based on platform"""
    system = platform.system()
    if system == "Windows":
        local_app_data = os.environ.get('LOCALAPPDATA', '')
        if local_app_data:
            chromedriver_path = os.path.join(local_app_data, "ChromeDriver", "chromedriver.exe")
            if os.path.exists(chromedriver_path):
                return chromedriver_path
    else:
        home_dir = os.path.expanduser("~")
        chromedriver_path = os.path.join(home_dir, ".chromedriver", "chromedriver")
        if os.path.exists(chromedriver_path):
            return chromedriver_path

    return None


def setup_webdriver():
    """Set up and return a configured Chrome webdriver"""
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--no-sandbox')
    options.add_argument('--disable-dev-shm-usage')
    options.add_argument('--disable-gpu')
    options.add_argument('--window-size=1920,1080')
    options.add_argument('--disable-blink-features=AutomationControlled')
    options.add_argument(
        '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
        'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    )

    # Method 1: direct initialization
    try:
        driver = webdriver.Chrome(options=options)
        st.success("Chrome webdriver initialized successfully!")
        return driver
    except Exception:
        pass

    # Method 2: use existing chromedriver
    chromedriver_path = get_chromedriver_path()
    if chromedriver_path:
        try:
            service = Service(chromedriver_path)
            driver = webdriver.Chrome(service=service, options=options)
            return driver
        except Exception:
            pass

    # Method 3: webdriver-manager
    if webdriver_manager_available:
        try:
            service = Service(ChromeDriverManager().install())
            driver = webdriver.Chrome(service=service, options=options)
            return driver
        except Exception:
            pass

    # Method 4: platform-specific binaries
    system = platform.system()
    if system == "Windows":
        chrome_paths = [
            r"C:\Program Files\Google\Chrome\Application\chrome.exe",
            r"C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
            os.path.expandvars(r"%LOCALAPPDATA%\Google\Chrome\Application\chrome.exe")
        ]
        for path in chrome_paths:
            if os.path.exists(path):
                options.binary_location = path
                try:
                    driver = webdriver.Chrome(options=options)
                    return driver
                except Exception:
                    continue
    elif system == "Linux":
        for binary in ["/usr/bin/chromium", "/usr/bin/google-chrome"]:
            if os.path.exists(binary):
                options.binary_location = binary
                try:
                    driver = webdriver.Chrome(options=options)
                    return driver
                except Exception:
                    continue

    st.error("Failed to initialize Chrome webdriver. Please make sure Chrome is installed.")
    return None
