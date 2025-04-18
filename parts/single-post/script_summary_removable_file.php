<script src="<?php echo siteurl(); ?>/assets/js/tesseract.min.js"></script>
<h1>Upload a Scanned PDF to Summarize</h1>
<input type="file" id="pdf-upload" accept=".pdf">
<h2>Summary:</h2>
<div id="summary" style="border: 1px solid #ccc; padding: 10px; margin-top: 10px; max-height: 300px; overflow-y: auto;"></div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const pdfUpload = document.getElementById('pdf-upload');
        const summaryDiv = document.getElementById('summary');

        pdfUpload.addEventListener('change', async (event) => {
            const file = event.target.files[0];

            if (file && file.type === "application/pdf") {
                try {
                    summaryDiv.textContent = "Processing PDF...";
                    const pdfData = await readFileAsArrayBuffer(file);
                    
                    // Extract text and normalize
                    const extractedText = await extractText(pdfData);

                    if (!extractedText.trim()) {
                        summaryDiv.textContent = "Unable to extract text from this PDF. Please try another file.";
                        return;
                    }

                    // Correct Arabic text order
                    const correctedText = normalizeAndFixArabicOrder(extractedText);
                    const summary = summarizeInChunks(correctedText);

                    // Display text in RTL
                    summaryDiv.innerHTML = `<div dir="rtl" style="text-align: right;">${summary || 'No summary could be generated.'}</div>`;
                } catch (error) {
                    summaryDiv.textContent = 'Error processing the PDF. Please try again.';
                    console.error(error);
                }
            } else {
                alert('Please upload a valid PDF file.');
            }
        });

        // Helper: Read file as ArrayBuffer
        function readFileAsArrayBuffer(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(new Uint8Array(e.target.result));
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        }

        // Main: Extract text with PDF.js first, fallback to OCR if needed
        async function extractText(pdfData) {
            const pdfText = await extractTextWithPdfJsOnly(pdfData);
            if (pdfText.trim()) {
                return pdfText; // Use extracted text if available
            }

            return await extractTextWithTesseract(pdfData);
        }

        // PDF.js Text Extraction (for selectable text PDFs)
        async function extractTextWithPdfJsOnly(pdfData) {
            const pdf = await pdfjsLib.getDocument(pdfData).promise;
            let textContent = '';
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const text = await page.getTextContent();
                textContent += text.items.map(item => item.str).join(' ') + ' ';
            }
            return textContent.trim();
        }

        // OCR Extraction using Tesseract.js (fallback)
        async function extractTextWithTesseract(pdfData) {
            const pdf = await pdfjsLib.getDocument(pdfData).promise;
            let fullText = '';

            // Loop through each page and extract text with OCR
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const viewport = page.getViewport({ scale: 1.0 }); // Lower resolution to speed up OCR
                const canvas = document.createElement("canvas");
                const context = canvas.getContext("2d");
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render page to canvas
                await page.render({ canvasContext: context, viewport: viewport }).promise;

                // Use Tesseract.js to extract text from the canvas image
                const text = await runTesseractOCR(canvas);
                fullText += text + ' ';
            }

            return fullText.trim();
        }

        // Helper: Run Tesseract.js OCR on an image (canvas)
        function runTesseractOCR(canvas) {
            return new Promise((resolve, reject) => {
                Tesseract.recognize(
                    canvas,
                    'ara', // Language code for Arabic
                    {
                        logger: (m) => console.log(m), // Log OCR progress
                    }
                ).then(({ data: { text } }) => {
                    resolve(text);
                }).catch(reject);
            });
        }

        // Helper: Normalize and Fix Arabic Word Order
        function normalizeAndFixArabicOrder(text) {
            // Normalize the text to handle diacritics
            const normalizedText = text.normalize("NFC");
            
            // Split the text into words, reverse the order, and rejoin
            const words = normalizedText.split(' ');
            const correctedText = words.reverse().join(' ');
            
            return correctedText;
        }

        // Helper: Summarize extracted text into chunks
        function summarizeInChunks(text, chunkSize = 5000) {
            const chunks = [];
            for (let i = 0; i < text.length; i += chunkSize) {
                chunks.push(text.slice(i, i + chunkSize));
            }

            let combinedSummary = '';
            chunks.forEach(chunk => {
                const sentences = chunk.split(/[.؟!\n]+/); // Split by sentence delimiters
                combinedSummary += sentences.slice(0, 5).join('. ') + '.\n'; // Take first 5 sentences per chunk
            });

            return combinedSummary.trim();
        }
    });
</script>