<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion - {{ $course->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .certificate-container {
            background: white;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .certificate {
            padding: 60px;
            position: relative;
            background: 
                radial-gradient(circle at 20% 80%, rgba(106, 90, 205, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(138, 108, 207, 0.1) 0%, transparent 50%),
                linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        }

        .certificate-border {
            position: absolute;
            top: 30px;
            left: 30px;
            right: 30px;
            bottom: 30px;
            border: 3px solid #6a5acd;
            border-radius: 8px;
        }

        .certificate-border::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 1px solid #8a6ccf;
            border-radius: 4px;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .certificate-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #6a5acd, #8a6ccf);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 10px 25px rgba(106, 90, 205, 0.3);
        }

        .certificate-title {
            font-size: 2.8rem;
            color: #6a5acd;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .certificate-subtitle {
            font-size: 1.2rem;
            color: #666;
            font-style: italic;
        }

        .certificate-content {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .certificate-text {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .student-name {
            font-size: 2.2rem;
            color: #333;
            font-weight: bold;
            margin: 25px 0;
            padding: 15px 0;
            border-bottom: 2px solid #6a5acd;
            border-top: 2px solid #6a5acd;
            position: relative;
        }

        .student-name::before,
        .student-name::after {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #8a6ccf, transparent);
        }

        .student-name::before {
            top: -10px;
        }

        .student-name::after {
            bottom: -10px;
        }

        .course-title {
            font-size: 1.8rem;
            color: #6a5acd;
            font-weight: bold;
            margin: 25px 0;
            font-style: italic;
        }



        .certificate-footer {
            display: flex;
            justify-content: center;
            align-items: end;
            margin-top: 50px;
            position: relative;
            z-index: 1;
        }

        .signature-section {
            text-align: center;
            min-width: 200px;
        }



        .signature-name {
            font-size: 1.1rem;
            color: #333;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }





        .action-buttons {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6a5acd, #8a6ccf);
            color: white;
            box-shadow: 0 4px 15px rgba(106, 90, 205, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 90, 205, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #6a5acd;
            border: 2px solid #6a5acd;
        }

        .btn-secondary:hover {
            background: #6a5acd;
            color: white;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .certificate-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
                width: 100%;
            }

            .action-buttons {
                display: none !important;
            }


        }

        /* Decorative elements */
        .certificate::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            width: 60px;
            height: 60px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%236a5acd"><path d="M12 2L3.09 8.26L4 21L12 17L20 21L20.91 8.26L12 2Z"/></svg>') no-repeat;
            background-size: contain;
            opacity: 0.1;
        }

        .certificate::after {
            content: '';
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%238a6ccf"><path d="M9.5 3A6.5 6.5 0 0 1 16 9.5c0 1.61-.59 3.09-1.56 4.23l.27.27h.79l5 5-1.5 1.5-5-5v-.79l-.27-.27A6.516 6.516 0 0 1 9.5 16 6.5 6.5 0 0 1 3 9.5 6.5 6.5 0 0 1 9.5 3m0 2C7.01 5 5 7.01 5 9.5S7.01 14 9.5 14 14 11.99 14 9.5 11.99 5 9.5 5Z"/></svg>') no-repeat;
            background-size: contain;
            opacity: 0.1;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .certificate {
                padding: 30px 20px;
            }

            .certificate-border {
                top: 15px;
                left: 15px;
                right: 15px;
                bottom: 15px;
            }

            .certificate-title {
                font-size: 2rem;
            }

            .student-name {
                font-size: 1.8rem;
            }

            .course-title {
                font-size: 1.4rem;
            }

            .certificate-footer {
                justify-content: center;
                text-align: center;
            }
        }
    </style>

</head>
<body>
    <div class="certificate-container" id="certificate">
        <div class="certificate">
            <div class="certificate-border"></div>
            
            <div class="certificate-header">
                <div class="certificate-logo">
                    🎓
                </div>
                <h1 class="certificate-title">Certificate of Completion</h1>
                <p class="certificate-subtitle">{{ config('app.name', 'LMS Prototype') }}</p>
            </div>

            <div class="certificate-content">
                <p class="certificate-text">This is to certify that</p>
                <h2 class="student-name">{{ $student->name }}</h2>
                <p class="certificate-text">has successfully completed the course</p>
                <h3 class="course-title">"{{ $course->title }}"</h3>
                

            </div>

            <div class="certificate-footer">
                <div class="signature-section">
                    <div class="signature-name">{{ $course->teacher->name ?? 'Course Instructor' }}</div>
                    <div class="signature-title">Course Instructor</div>
                </div>
            </div>


        </div>
    </div>

    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
            </svg>
            Print Certificate
        </button>
        <a href="/student/certificates" class="btn btn-secondary">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Certificates
        </a>
    </div>

    <script>
        // Add some animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const certificate = document.querySelector('.certificate');
            certificate.style.opacity = '0';
            certificate.style.transform = 'scale(0.9)';
            certificate.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                certificate.style.opacity = '1';
                certificate.style.transform = 'scale(1)';
            }, 100);
        });
    </script>
</body>
</html> 