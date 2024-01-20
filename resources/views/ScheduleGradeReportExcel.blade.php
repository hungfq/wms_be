<table>
    <tr>
        <td colspan="3" style="text-align: center;">TRƯỜNG ĐẠI HỌC SPKT TP.HCM</td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: center; font-weight: bold;">KHOA CÔNG NGHỆ THÔNG TIN</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td colspan="4" style="text-align: right;"><i>{{ $locationTime }}</i></td>
    </tr>
    <tr></tr>
    <tr>
        <td colspan="18" style="text-align: center; font-weight: bold;">DANH SÁCH ĐIỂM
            {{ mb_strtoupper(data_get($schedule, 'name', '') . ' (' .data_get($schedule, 'code', '') . ')') }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td colspan="2" height="30" style="text-align: center;">
        {{--            Mã học phần: <br/> GRPR471979</td>--}}
    </tr>
    <tr></tr>
    <tr>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            STT
        </td>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            MSSV
        </td>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Tên sinh viên
        </td>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Mã nhóm
        </td>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Tên đề tài
        </td>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Điểm trung bình
        </td>
        <td colspan="3" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Giảng viên hướng dẫn
        </td>
        <td colspan="3" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Giảng viên Phản biện
        </td>
        <td colspan="3" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Chủ tịch hội đồng
        </td>
        <td colspan="3" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Thư ký hội đồng
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Mã GV</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Tên Giảng viên</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Điểm</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Mã GV</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Tên Giảng viên</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Điểm</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Mã GV</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Tên Giảng viên</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Điểm</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Mã GV</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Tên Giảng viên</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Điểm</td>
    </tr>
    @php $index = 0; @endphp
    @foreach($topics as $topic)
        @php
            $count = $topic->students->count();
            $students = $topic->students;
            $averageGrade = null;
            if (isset($topic->lecturer_grade)
                && isset($topic->critical_grade)
                && isset($topic->committee_president_grade)
                && isset($topic->committee_secretary_grade)) {
                $averageGrade = number_format(((int)$topic->lecturer_grade
                        + (int)$topic->critical_grade
                        + (int)$topic->committee_president_grade
                        + (int)$topic->committee_secretary_grade) / 4, 2);
        }
        @endphp

        @foreach($students as $studentIndex => $student)
            @if($studentIndex == 0)
                <tr>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ ++$index }}</td>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($student, 'code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;">{{ data_get($student, 'name') }}</td>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'title') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ $averageGrade }}</td>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'lecturer.code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'lecturer.name') }}</td>
                    <td style="vertical-align: center; border: 1px solid black; text-align: center;"
                        rowspan="{{$count}}">{{ data_get($topic, 'lecturer_grade') }}</td>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'critical.code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'critical.name') }}</td>
                    <td style="vertical-align: center; border: 1px solid black; text-align: center;"
                        rowspan="{{$count}}">{{ data_get($topic, 'critical_grade') }}</td>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'committee.president.code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'committee.president.name') }}</td>
                    <td style="vertical-align: center; border: 1px solid black; text-align: center;"
                        rowspan="{{$count}}">{{ data_get($topic, 'committee_president_grade') }}</td>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'committee.secretary.code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;"
                        rowspan="{{$count}}">{{ data_get($topic, 'committee.secretary.name') }}</td>
                    <td style="vertical-align: center; border: 1px solid black; text-align: center;"
                        rowspan="{{$count}}">{{ data_get($topic, 'committee_secretary_grade') }}</td>
                </tr>
            @else
                <tr>
                    <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($student, 'code') }}</td>
                    <td style="vertical-align: center; border: 1px solid black;">{{ data_get($student, 'name') }}</td>
                </tr>
            @endif
        @endforeach
    @endforeach
</table>

