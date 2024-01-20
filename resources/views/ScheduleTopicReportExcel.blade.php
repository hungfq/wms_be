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
        <td colspan="2" style="text-align: right;"><i>{{ $locationTime }}</i></td>
    </tr>
    <tr></tr>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: bold;">DANH SÁCH SINH VIÊN ĐĂNG KÝ ĐỀ TÀI
            {{ mb_strtoupper(data_get($schedule, 'name', '') . ' (' .data_get($schedule, 'code', '') . ')') }}
        </td>
    </tr>
    {{--    <tr>--}}
    {{--        <td colspan="9" style="text-align: center; font-weight: bold;">HỌC KỲ: 2</td>--}}
    {{--    </tr>--}}
    {{--    <tr>--}}
    {{--        <td colspan="9" style="text-align: center; font-weight: bold;">NĂM HỌC: 2022-2023</td>--}}
    {{--    </tr>--}}
    <tr></tr>
    <tr>
        <td colspan="2" height="30" style="text-align: center;">
{{--            Mã học phần: <br/> GRPR471979--}}
        </td>
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
        <td colspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Giảng viên hướng dẫn
        </td>
        <td colspan="2" style="font-weight: bold; text-align: center; vertical-align: center; border: 1px solid black;">
            Giảng viên Phản biện
        </td>
    </tr>
    <tr>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Mã GV</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Tên Giảng viên</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Mã GV</td>
        <td style="font-weight: bold; text-align: center; border: 1px solid black;">Tên Giảng viên</td>
    </tr>
    @foreach($topics as $index => $topic)
        @php
            $count = $topic->students->count();
            $students = $topic->students;
        @endphp
        @if($count == 0)
            <tr>
                <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ $index + 1 }}</td>
                <td style="vertical-align: center; text-align: center; border: 1px solid black;"></td>
                <td style="vertical-align: center; border: 1px solid black;"></td>
                <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($topic, 'code') }}</td>
                <td style="vertical-align: center; border: 1px solid black;">{{ data_get($topic, 'title') }}</td>
                <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($topic, 'lecturer.code') }}</td>
                <td style="vertical-align: center; border: 1px solid black;">{{ data_get($topic, 'lecturer.name') }}</td>
                <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($topic, 'critical.code') }}</td>
                <td style="vertical-align: center; border: 1px solid black;">{{ data_get($topic, 'critical.name') }}</td>
            </tr>
        @else
            @foreach($students as $studentIndex => $student)
                @if($studentIndex == 0)
                    <tr>
                        <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ $index + 1 }}</td>
                        <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($student, 'code') }}</td>
                        <td style="vertical-align: center; border: 1px solid black;">{{ data_get($student, 'name') }}</td>
                        <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ data_get($topic, 'code') }}</td>
                        <td style="vertical-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ data_get($topic, 'title') }}</td>
                        <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ data_get($topic, 'lecturer.code') }}</td>
                        <td style="vertical-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ data_get($topic, 'lecturer.name') }}</td>
                        <td style="vertical-align: center; text-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ data_get($topic, 'critical.code') }}</td>
                        <td style="vertical-align: center; border: 1px solid black;"
                            rowspan="{{$count}}">{{ data_get($topic, 'critical.name') }}</td>
                    </tr>
                @else
                    <tr>
                        <td style="vertical-align: center; text-align: center; border: 1px solid black;">{{ data_get($student, 'code') }}</td>
                        <td style="vertical-align: center; border: 1px solid black;">{{ data_get($student, 'name') }}</td>
                    </tr>
                @endif
            @endforeach
        @endif
    @endforeach
</table>

