<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S1 END OF TERM II RESULTS</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Arial&display=swap');

        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            -webkit-print-color-adjust: exact;
        }

        .report-card {
            width: 210mm;
            height: 297mm;
            margin: auto;
            background: white;
            padding: 15mm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 4px double #004080;
        }

        .header .school-info {
            text-align: center;
            flex-grow: 1;
        }

        .header .school-info h1 {
            margin: 0;
            font-size: 24px;
            color: #004080;
        }

        .header .school-info p {
            margin: 2px 0;
            font-size: 12px;
        }

        .header .school-info .motto {
            font-style: italic;
            font-size: 14px;
            color: #0056b3;
        }

        .header .logo {
            width: 80px;
            height: 80px;
        }

        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            color: #004080;
            text-transform: uppercase;
        }

        .student-info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f2f6fa;
            border-radius: 5px;
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: auto 1fr auto 1fr;
            grid-gap: 5px 15px;
            font-size: 12px;
            flex-grow: 1;
        }

        .student-info-grid > div > b {
            color: #004080;
        }

        .student-photo {
            width: 100px;
            height: 100px;
            border: 3px solid #004080;
            border-radius: 5px;
            margin-left: 20px;
        }

        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }

        .assessment-table th, .assessment-table td {
            border: 1px solid #dee2e6;
            padding: 6px;
            text-align: center;
        }

        .assessment-table th {
            background-color: #004080;
            color: white;
            font-weight: bold;
        }

        .assessment-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .assessment-table .subject-col {
            text-align: left;
            font-weight: bold;
        }

        .grade-A { background-color: #d4edda; color: #155724; }
        .grade-B { background-color: #cce5ff; color: #004085; }
        .grade-C { background-color: #fff3cd; color: #856404; }
        .grade-D { background-color: #ffeeba; color: #856404; }
        .grade-E { background-color: #f8d7da; color: #721c24; }

        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .summary-box {
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 5px;
            width: 24%;
            text-align: center;
        }

        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #004080;
        }

        .summary-box p {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .remarks-section {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .remark-box {
            flex: 1;
            padding: 15px;
            border-left: 4px solid #004080;
            background-color: #f8f9fa;
            border-radius: 0 5px 5px 0;
        }

        .remark-box h4 {
            margin: 0 0 5px 0;
            color: #004080;
        }

        .remark-box p {
            margin: 0;
            font-style: italic;
        }

        .footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            font-size: 10px;
            text-align: center;
        }

        .grading-scale {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 10px;
        }

        .grading-scale td {
            border: 1px solid #ccc;
            padding: 4px;
        }

        .grading-scale .scale-header {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .curriculum-note {
            font-style: italic;
            color: #6c757d;
        }

        @media print {
            .report-card {
                page-break-after: always;
                box-shadow: none;
            }
            .actions-container {
                display: none;
            }
        }

        .actions-container {
            padding: 10px 20px;
            text-align: right;
            background: #e9ecef;
            border-bottom: 1px solid #dee2e6;
            width: 210mm;
            margin: auto;
            box-sizing: border-box;
        }

        .actions-container button {
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            border: 1px solid #004080;
            background-color: #004080;
            color: white;
            border-radius: 4px;
        }
        .actions-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="actions-container">
        <button onclick="window.print()">Print Reports</button>
        <button id="download-pdf">Download as PDF</button>
    </div>

    <div class="report-card">
        <div class="header">
            <img src="<?php echo htmlspecialchars($school_logo_url); ?>" alt="School Logo" class="logo">
            <div class="school-info">
                <h1>ST JOSEPH VOC. SEC SCHOOL - NYAMITYOBORA</h1>
                <p><b>SCH.ID:</b> 1002215 | <b>TEL:</b> 0704050747 | <b>Email:</b> st.josephthejust@gmail.com | <b>P.O BOX</b> 406 MBARARA</p>
                <p class="motto">"WITHOUT JESUS WHAT CAN THE WORLD GIVE YOU"</p>
            </div>
        </div>

        <h2 class="report-title">
            <?php echo htmlspecialchars($report_title); ?>
        </h2>

        <div class="student-info-section">
            <div class="student-info-grid">
                <div><b>Name:</b></div>
                <div><?php echo htmlspecialchars($student['name']); ?></div>
                <div><b>Scholar ID:</b></div>
                <div><?php echo htmlspecialchars($student['scholar_id']); ?></div>

                <div><b>Class:</b></div>
                <div><?php echo htmlspecialchars($student['class']); ?></div>
                <div><b>Term:</b></div>
                <div><?php echo htmlspecialchars($student['term']); ?></div>

                <div><b>LIN/AMIS No:</b></div>
                <div><?php echo htmlspecialchars($student['lin']); ?></div>
                <div><b>Date:</b></div>
                <div><?php echo htmlspecialchars($student['date']); ?></div>
            </div>
            <img src="<?php echo htmlspecialchars($student['photo_url']); ?>" alt="Student Photo" class="student-photo">
        </div>

        <table class="assessment-table">
            <thead>
                <tr>
                    <th rowspan="2" class="subject-col">Subject</th>
                    <th colspan="5">Formative Assessment</th>
                    <th>Summative</th>
                    <th rowspan="2">Final (100)</th>
                    <th rowspan="2">Grade</th>
                    <th rowspan="2">Grade Descriptor</th>
                    <th rowspan="2">TR</th>
                </tr>
                <tr>
                    <th>T1</th>
                    <th>T2</th>
                    <th>T3</th>
                    <th>AS</th>
                    <th>FA (20)</th>
                    <th>EOT2 (80)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td class="subject-col"><?php echo htmlspecialchars($subject['subject']); ?></td>
                    <td><?php echo $subject['t1']; ?></td>
                    <td><?php echo $subject['t2']; ?></td>
                    <td><?php echo $subject['t3']; ?></td>
                    <td><?php echo $subject['as']; ?></td>
                    <td><b><?php echo $subject['fa_20']; ?></b></td>
                    <td><b><?php echo $subject['eot2_80']; ?></b></td>
                    <td><b><?php echo $subject['final_100']; ?></b></td>
                    <td class="grade-<?php echo htmlspecialchars($subject['grade']); ?>"><?php echo htmlspecialchars($subject['grade']); ?></td>
                    <td><?php echo htmlspecialchars($subject['descriptor']); ?></td>
                    <td><?php echo htmlspecialchars($subject['tr']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-section">
             <div class="summary-box">
                <h3>Overall Average</h3>
                <p><?php echo $overall_average; ?>%</p>
            </div>
            <div class="summary-box">
                <h3>Level of Achievement</h3>
                <p><?php echo htmlspecialchars($level_achievement); ?></p>
            </div>
             <div class="summary-box">
                <h3>Next Term Begins</h3>
                <p><?php echo htmlspecialchars($next_term_begins); ?></p>
            </div>
            <div class="summary-box">
                <h3>Next Term Ends</h3>
                <p><?php echo htmlspecialchars($next_term_ends); ?></p>
            </div>
        </div>

        <div class="remarks-section">
            <div class="remark-box">
                <h4>Class Teacher's Remarks</h4>
                <p>"<?php echo htmlspecialchars($class_teacher_remarks); ?>"</p>
            </div>
            <div class="remark-box">
                <h4>Headteacher's Remarks</h4>
                <p>"<?php echo htmlspecialchars($headteacher_remarks); ?>"</p>
            </div>
        </div>

        <div class="footer">
            <table class="grading-scale">
                <tr>
                    <td class="scale-header">A</td>
                    <td>Exceptional (80-100)</td>
                    <td class="scale-header">B</td>
                    <td>Outstanding (70-79)</td>
                    <td class="scale-header">C</td>
                    <td>Satisfactory (60-69)</td>
                    <td class="scale-header">D</td>
                    <td>Basic (50-59)</td>
                    <td class="scale-header">E</td>
                    <td>Elementary (&lt;50)</td>
                </tr>
            </table>
            <p><b>Key:</b> T = Topic Assessment; FA = Formative Assessment; EOT2 = End of Term II; TR = Teacher's Initials</p>
            <p class="curriculum-note">Based on Uganda's New Lower Secondary Competency-Based Curriculum.</p>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        document.getElementById('download-pdf').addEventListener('click', function () {
            // To handle multiple report cards, we need to process all of them.
            // We create a temporary container to hold all report cards for PDF generation.
            const container = document.createElement('div');
            const elements = document.querySelectorAll('.report-card');
            elements.forEach(el => container.appendChild(el.cloneNode(true)));

            const opt = {
                margin: 0,
                filename: 'report_cards.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().from(container).set(opt).save();
        });
    </script>
</body>
</html>
