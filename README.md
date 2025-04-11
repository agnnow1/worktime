API Documentation
This API allows you to create employees, register work hours, and retrieve summaries of work hours on a daily and monthly basis.


Endpoints

1. POST /api/employee
Create a new employee.

Body:
json
{
"firstname": "monika",
"lastname": "pieniniska",
"pesel": "12312312121"
}

Response:
Status Code: 201 (Created)

{
"id": "01962641-d390-7306-bfa0-295171692550"
}


2. POST /api/work-time
Register work hours for an employee.

Body:
json
{
"employee_id": "01962641-d390-7306-bfa0-295171692550",
"start_time": "23.01.1970 08:00",
"end_time": "23.01.1970 14:00"
}

Response:
Status Code: 201 (Created)

{
"message": "Work time created"
}


3. GET /api/work-time/summary/day
Get the daily work time summary for an employee.

Query Parameters:

employee_id: The unique identifier of the employee (UUID).

work_day: The date in the format dd.mm.yyyy.

Example Request:
GET /api/work-time/summary/day?employee_id=01962641-d390-7306-bfa0-295171692550&work_day=23.01.1970

Response:
Status Code: 200 (OK)

{
"response": {
"total_after_calculation": "120 PLN",
"hours_for_the_given_day": 6,
"hourly_rate": "20 PLN"
}
}

4. GET /api/work-time/summary/month
Get the monthly work time summary for an employee.

Query Parameters:

employee_id: The unique identifier of the employee (UUID).

date: The month in the format mm.yyyy.

Example Request:

GET /api/work-time/summary/month?employee_id=01962641-d390-7306-bfa0-295171692550&date=01.1970
Response:
Status Code: 200 (OK)

{
"response": {
"normal_hours_in_the_given_month": 18,
"hourly_rate": "20 PLN",
"overtime_hours_in_the_given_month": 0,
"overtime_rate": "40 PLN",
"total_after_calculation": "360 PLN"
}
}


Error Responses
The API will return appropriate error responses when:

Required fields are missing.

The employee doesn't exist.

The work time exceeds the allowed daily limit.

Invalid date formats are provided.


Notes
The start_time and end_time should be in the format dd.mm.yyyy HH:MM when registering work time.

For the monthly summary, you must provide the date in the mm.yyyy format.

