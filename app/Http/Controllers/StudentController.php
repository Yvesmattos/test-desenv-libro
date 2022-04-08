<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{

    protected $rules = [
        'name' => 'required|max:120',
        'email' => 'required|max:150|email',
        'birth_date' => 'required|date',
        'gender' => 'string|max:1',
        'toAddStudents' => 'exists:courses,id',
        'toRemoveStudents' => 'exists:courses,id',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $students = Student::all();
        return $students;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);

        $student = new Student($validated);
        $student->save();

        $student->courses()->attach($validated['toAddStudents']);
        $student->courses()->detach($validated['toRemoveStudents']);

        return $student->courses;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        return $student;
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate($this->rules);
        $student->students()->detach($validated['courses']);

        $student->update($validated);

        return $student;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        $student->delete();
        $id = $student->id;

        return ['msg' => "Registro $id excluído"];
    }

    /**
     * Display a report that shows the total course's students divided by 
     * age and gender
     * @return \Illuminate\Http\Response
     */

    public function reportStudentsByCourse()
    {
        $courses = Course::all();

        $data = array();

        foreach ($courses as $course) {
            $data["Cursos"]["Nome curso"] = $course->title;
            $data["Cursos"]["Total Homens"] = Student::join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->where('gender', 'm')->count();
            $data["Cursos"]["Total Mulheres"] = Student::where('gender', 'f')->join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->count();
            $data["Cursos"]["Menor que 15 anos"]["Total"] =  Student::join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->where('birth_date', '>=', '2007-01-01')->count();
            $data["Cursos"]["Entre 15 e 18 anos"]["Total"] =  Student::join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->whereBetween('birth_date', ['2004-01-01', '2006-12-31'])->count();
            $data["Cursos"]["Entre 19 e 24 anos"]["Total"] =  Student::join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->whereBetween('birth_date', ['1998-01-01', '2003-12-31'])->count();
            $data["Cursos"]["Entre 25 e 30 anos"]["Total"] =  Student::join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->whereBetween('birth_date', ['1992-01-01', '1997-12-31'])->count();
            $data["Cursos"]["Maior que 30 anos"]["Total"] =  Student::join('course_student', 'students.id', '=', 'course_student.student_id')->where('course_student.course_id', "$course->id")->where('birth_date', '<', '1992-01-01')->count();
        }

        return $data;
    }

    public function findByNameOrEmail($param)
    {
        if (strpos($param, '@') !== false) {
            return Student::where('email', $param)->first();
        } else {
            return Student::where('name', $param)->first();
        }
    }
}
