<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\ParentCallLog;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Mark;

class InstallController
{
    public function showInstall(Request $request): void
    {
        $requirements = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO SQLite' => extension_loaded('pdo_sqlite'),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'MBString Extension' => extension_loaded('mbstring'),
        ];

        View::render('install.index', [
            'title' => 'Install Class Aid',
            'requirements' => $requirements,
            'canProceed' => $requirements['PHP >= 8.2'] && $requirements['PDO Extension'],
        ], 'install');
    }

    public function processInstall(Request $request): void
    {
        $dbType = $request->input('db_type', 'sqlite');
        $teacherName = trim((string) $request->input('teacher_name'));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        $schoolName = trim((string) $request->input('school_name', 'Springdale Academy'));
        $seedDemo = (bool) $request->input('seed_demo', false);

        if (empty($teacherName) || empty($email) || empty($password)) {
            Session::flash('error', 'Please fill in all required teacher fields.');
            Response::redirect('/install');
        }

        // Prepare .env values
        $envData = [
            'APP_NAME' => 'Class Aid',
            'APP_ENV' => 'production',
            'APP_KEY' => base64_encode(random_bytes(32)),
            'APP_DEBUG' => 'false',
            'APP_URL' => 'http://localhost:8000',
            'APP_INSTALLED' => 'true',
            'DB_CONNECTION' => $dbType,
        ];

        if ($dbType === 'sqlite') {
            $dbPath = 'database/database.sqlite';
            $envData['DB_DATABASE'] = $dbPath;
            $test = Database::testConnection('sqlite', ['database' => $dbPath]);
            if (!$test['success']) {
                Session::flash('error', 'SQLite Error: ' . $test['message']);
                Response::redirect('/install');
            }
        } else {
            $host = trim((string) $request->input('mysql_host', '127.0.0.1'));
            $port = trim((string) $request->input('mysql_port', '3306'));
            $dbName = trim((string) $request->input('mysql_name', 'class_aid'));
            $user = trim((string) $request->input('mysql_user', 'root'));
            $pass = (string) $request->input('mysql_password', '');

            $test = Database::testConnection('mysql', [
                'host' => $host,
                'port' => $port,
                'name' => $dbName,
                'user' => $user,
                'password' => $pass,
            ]);

            if (!$test['success']) {
                Session::flash('error', 'MySQL Connection Failed: ' . $test['message']);
                Response::redirect('/install');
            }

            $envData['DB_HOST'] = $host;
            $envData['DB_PORT'] = $port;
            $envData['DB_NAME'] = $dbName;
            $envData['DB_USER'] = $user;
            $envData['DB_PASSWORD'] = $pass;
        }

        // Write .env
        Config::writeEnv($envData);
        Config::load(Config::get('BASE_DIR'));
        Database::resetInstance();

        // Run migrations
        try {
            Database::runMigrations($dbType);
        } catch (\Exception $e) {
            Session::flash('error', 'Migration Error: ' . $e->getMessage());
            Response::redirect('/install');
        }

        // Create initial Teacher User
        $teacherId = User::create([
            'name' => $teacherName,
            'email' => $email,
            'password' => $password,
            'role' => 'teacher',
            'school_name' => $schoolName,
        ]);

        // Seed initial classes or demo dataset
        $this->seedInitialData($teacherId, $seedDemo);

        // Auto login
        $user = User::findById($teacherId);
        if ($user) {
            Session::setAuthUser($user);
        }

        Session::flash('success', 'Class Aid has been successfully initialized! Welcome to your digital teaching assistant.');
        Response::redirect('/dashboard');
    }

    private function seedInitialData(int $teacherId, bool $seedDemo): void
    {
        // 1. Primary Home Class (with ~30 students as requested by user)
        $homeClassId = SchoolClass::create([
            'teacher_id' => $teacherId,
            'name' => 'Grade 10',
            'section' => 'A',
            'academic_year' => '2026-2027',
            'is_home_class' => 1,
        ]);

        // Add subjects to Home Class (demonstrating multi-subject per class)
        $mathSubId = Subject::create(['class_id' => $homeClassId, 'name' => 'Mathematics', 'code' => 'MTH-10']);
        $csSubId = Subject::create(['class_id' => $homeClassId, 'name' => 'Computer Science', 'code' => 'CS-10']);
        $sciSubId = Subject::create(['class_id' => $homeClassId, 'name' => 'General Science', 'code' => 'SCI-10']);

        // 2. The 4 other classes managed by this teacher
        $otherClasses = [
            ['name' => 'Grade 9', 'section' => 'A', 'subject' => 'Mathematics', 'code' => 'MTH-09'],
            ['name' => 'Grade 9', 'section' => 'B', 'subject' => 'Mathematics', 'code' => 'MTH-09B'],
            ['name' => 'Grade 11', 'section' => 'A', 'subject' => 'Computer Science', 'code' => 'CS-11'],
            ['name' => 'Grade 12', 'section' => 'A', 'subject' => 'Informatics Practices', 'code' => 'IP-12'],
        ];

        $otherClassIds = [];
        foreach ($otherClasses as $oc) {
            $cId = SchoolClass::create([
                'teacher_id' => $teacherId,
                'name' => $oc['name'],
                'section' => $oc['section'],
                'academic_year' => '2026-2027',
                'is_home_class' => 0,
            ]);
            Subject::create(['class_id' => $cId, 'name' => $oc['subject'], 'code' => $oc['code']]);
            $otherClassIds[] = $cId;
        }

        if (!$seedDemo) {
            return;
        }

        // Demo Student names for primary home class (32 students)
        $firstNames = [
            'Aarav', 'Fatima', 'Liam', 'Zainab', 'Alexander', 'Sophia', 'Rohan', 'Amina',
            'Noah', 'Maya', 'Ethan', 'Layla', 'Lucas', 'Zara', 'Mason', 'Chloe',
            'Oliver', 'Ananya', 'Elijah', 'Sara', 'James', 'Kavya', 'Benjamin', 'Hiba',
            'William', 'Meera', 'Henry', 'Noor', 'Daniel', 'Ayesha', 'Samuel', 'Priya'
        ];
        $lastNames = [
            'Khan', 'Patel', 'Smith', 'Ahmed', 'Johnson', 'Sharma', 'Williams', 'Malik',
            'Brown', 'Verma', 'Jones', 'Ali', 'Garcia', 'Singh', 'Miller', 'Davis',
            'Rodriguez', 'Choudhury', 'Martinez', 'Qureshi', 'Hernandez', 'Joshi', 'Lopez', 'Syed',
            'Gonzalez', 'Rao', 'Wilson', 'Farooq', 'Anderson', 'Tariq', 'Thomas', 'Deshmukh'
        ];

        $createdStudentIds = [];
        for ($i = 0; $i < 32; $i++) {
            $fName = $firstNames[$i];
            $lName = $lastNames[$i];
            $adminId = sprintf('ADM-2026-%03d', $i + 1);
            $gender = ($i % 2 === 0) ? 'Male' : 'Female';
            $phone = sprintf('+1 555-01%02d', 10 + $i);
            $guardianName = ($gender === 'Male') ? 'Mr. ' . $lName : 'Mrs. ' . $lName;

            $sId = Student::create([
                'student_admin_id' => $adminId,
                'class_id' => $homeClassId,
                'first_name' => $fName,
                'last_name' => $lName,
                'gender' => $gender,
                'dob' => '2010-' . sprintf('%02d', ($i % 12) + 1) . '-' . sprintf('%02d', ($i % 28) + 1),
                'primary_guardian_name' => $guardianName,
                'primary_guardian_relation' => ($i % 2 === 0) ? 'Father' : 'Mother',
                'primary_phone' => $phone,
                'secondary_phone' => sprintf('+1 555-02%02d', 10 + $i),
                'guardian_email' => strtolower($fName . '.' . $lName . '@example.com'),
                'address' => (100 + $i) . ' Academic Ave, Education City',
                'status' => 'active',
            ]);
            $createdStudentIds[] = $sId;
        }

        // Seed 10 students into each of the 4 other classes
        foreach ($otherClassIds as $idx => $ocId) {
            for ($k = 0; $k < 10; $k++) {
                $num = 100 + ($idx * 10) + $k;
                Student::create([
                    'student_admin_id' => 'STU-' . $num, // Demonstrating different ID format
                    'class_id' => $ocId,
                    'first_name' => 'Student' . $num,
                    'last_name' => 'Class' . ($idx + 1),
                    'gender' => ($k % 2 === 0) ? 'Male' : 'Female',
                    'primary_guardian_name' => 'Guardian of ' . $num,
                    'primary_guardian_relation' => 'Parent',
                    'primary_phone' => '+1 555-03' . sprintf('%02d', $k),
                    'status' => 'active',
                ]);
            }
        }

        // Seed realistic Attendance records for Home Class for the past 5 days
        $days = [
            date('Y-m-d', strtotime('-4 days')),
            date('Y-m-d', strtotime('-3 days')),
            date('Y-m-d', strtotime('-2 days')),
            date('Y-m-d', strtotime('-1 days')),
            date('Y-m-d'),
        ];

        foreach ($days as $dIdx => $d) {
            $batch = [];
            foreach ($createdStudentIds as $sIdx => $sId) {
                // Let student index 3 (Zainab) have 3 consecutive absences to trigger the chronic flag!
                if ($sIdx === 3 && $dIdx >= 2) {
                    $status = 'A';
                    $remarks = 'Unexcused - consecutive day ' . ($dIdx - 1);
                } elseif ($sIdx === 7 && $dIdx === 4) {
                    $status = 'L';
                    $remarks = 'Late 15 mins (bus delay)';
                } elseif ($sIdx === 12 && $dIdx === 3) {
                    $status = 'A';
                    $remarks = 'Fever';
                } else {
                    $status = 'P';
                    $remarks = null;
                }
                $batch[$sId] = ['status' => $status, 'remarks' => $remarks];
            }
            Attendance::recordBatch($homeClassId, $d, $batch);
        }

        // Seed realistic Parent Call Logs with diverse categories & responses
        $demoCalls = [
            [
                'student_id' => $createdStudentIds[3], // Zainab Ahmed (3 consecutive absentees)
                'contact_person_name' => 'Mrs. Ahmed',
                'relationship' => 'Mother',
                'phone_dialed' => '+1 555-0113',
                'call_timestamp' => date('Y-m-d 09:30:00', strtotime('-1 days')),
                'call_type' => 'Phone Call',
                'call_category' => 'Absenteeism',
                'discussion_summary' => 'Informed mother that Zainab has been absent for 3 consecutive days without prior leave request.',
                'parent_response' => 'Mother apologized and explained Zainab had severe stomach flu. Mother promised to send a physician medical slip tomorrow.',
                'follow_up_required' => 1,
                'follow_up_date' => date('Y-m-d', strtotime('+1 days')),
                'status' => 'Pending Follow-up',
            ],
            [
                'student_id' => $createdStudentIds[0], // Aarav Khan
                'contact_person_name' => 'Mr. Tariq Khan',
                'relationship' => 'Father',
                'phone_dialed' => '+1 555-0110',
                'call_timestamp' => date('Y-m-d 14:15:00', strtotime('-3 days')),
                'call_type' => 'Phone Call',
                'call_category' => 'Appreciation',
                'discussion_summary' => 'Commended Aarav on achieving highest score in Midterm Math Olympiad qualification.',
                'parent_response' => 'Father was thrilled and appreciated teacher mentorship. Mentioned they will encourage him for finals.',
                'follow_up_required' => 0,
                'follow_up_date' => null,
                'status' => 'Completed',
            ],
            [
                'student_id' => $createdStudentIds[6], // Rohan Sharma
                'contact_person_name' => 'Mrs. Sharma',
                'relationship' => 'Mother',
                'phone_dialed' => '+1 555-0116',
                'call_timestamp' => date('Y-m-d 11:00:00', strtotime('-5 days')),
                'call_type' => 'WhatsApp',
                'call_category' => 'Homework',
                'discussion_summary' => 'Sent reminder regarding pending Computer Science project submission deadline.',
                'parent_response' => 'Mother confirmed via message that Rohan completed the coding part and will submit on thumb drive on Monday.',
                'follow_up_required' => 0,
                'follow_up_date' => null,
                'status' => 'Completed',
            ],
            [
                'student_id' => $createdStudentIds[10], // Ethan Jones
                'contact_person_name' => 'Mr. Jones',
                'relationship' => 'Father',
                'phone_dialed' => '+1 555-0120',
                'call_timestamp' => date('Y-m-d 15:45:00', strtotime('-2 days')),
                'call_type' => 'Phone Call',
                'call_category' => 'Discipline',
                'discussion_summary' => 'Discussed repeated classroom distractions and talking during lectures.',
                'parent_response' => 'Father was receptive, stated he will have a firm conversation at home and requested weekly feedback.',
                'follow_up_required' => 1,
                'follow_up_date' => date('Y-m-d', strtotime('+5 days')),
                'status' => 'Pending Follow-up',
            ],
        ];

        foreach ($demoCalls as $call) {
            $call['teacher_id'] = $teacherId;
            ParentCallLog::create($call);
        }

        // Seed an Exam and Marks for Home Class in Mathematics
        $examId = Exam::create([
            'subject_id' => $mathSubId,
            'title' => 'Mid-Term Examination',
            'term' => 'Term 1',
            'max_marks' => 100.0,
            'pass_marks' => 40.0,
            'exam_date' => date('Y-m-d', strtotime('-7 days')),
        ]);

        $marksData = [];
        $scores = [98, 88, 76, 65, 82, 91, 95, 78, 84, 90, 68, 72, 85, 93, 77, 81, 89, 94, 62, 87, 92, 79, 83, 70, 75, 86, 96, 69, 74, 88, 90, 85];
        foreach ($createdStudentIds as $k => $sId) {
            $score = $scores[$k] ?? 75;
            $marksData[$sId] = [
                'marks' => $score,
                'remarks' => ($score >= 90) ? 'Outstanding performance' : (($score >= 75) ? 'Good effort' : 'Needs practice in algebra'),
            ];
        }
        Mark::recordBatch($examId, 100.0, 40.0, $marksData);
    }
}
