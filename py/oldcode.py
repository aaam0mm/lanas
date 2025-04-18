for index, pdf_file_path in enumerate(books_paths):
                            check_upload_stat = driver.ele('css:#composer-background div.h-8 > button[data-state="closed"].rounded-bl-xl', timeout=5)
                            if not check_upload_stat:
                                file_input.input(pdf_file_path)
                                time.sleep(5)
                                logging.info("PDF file upload initialized.")
                                circle = driver.ele('css:div > svg > circle', timeout=10)

                                if circle:
                                    logging.info("Upload progress started.")
                                    print("Upload progress started.")

                                    upload_complete = wait_for_upload_to_complete(driver, timeout=30)
                                    if upload_complete:
                                        print("File upload completed successfully.")
                                        text += f"\n الجزء رقم({index + 1}) "
                                    else:
                                        logging.warning("Upload progress indicator did not disappear in time.")
                                else:
                                    logging.error("Upload progress indicator did not appear.")
                            else:
                                logging.error("upload pdf disabled")