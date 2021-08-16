import { useEffect, useState } from 'react';
import classes from './Overview.module.css';
import Modal from '../../layout/Modal/Modal';
import { useForm } from 'react-hook-form';
import axios from 'axios';
import OverviewCard from './OverviewCard';
import Loader from '../../layout/Loader/Loader';

const Overview = () => {
	const [showModal, setShowModal] = useState(false);
	const [allEmployee, setAllEmployees] = useState([]);
	const { register, handleSubmit } = useForm();
	const [courses, setCourses] = useState([]);
	const [loading, setLoading] = useState(false);
	const [formLoadingState, setFormLoadingState] = useState(false);
	const [seatModal, setSeatModal] = useState(false);
	const [selectedCourse, setSelectedCourse] = useState('');

	const onSelectCourseToAssign = (courseID) => {
		setSelectedCourse(courseID);
		setSeatModal(true);
	};

	const userInfo = JSON.parse(localStorage.getItem('ccAuth'));

	const onAssignSeat = (userID) => {
		const assignCourseData = JSON.stringify({
			userID: userID,
			courseID: selectedCourse,
		});
		setFormLoadingState(true);

		axios
			.post(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/courseEnrollment',
				assignCourseData
			)
			.then((res) => {
				console.log(res);
				setFormLoadingState(false);
				setSeatModal(false);
				setSelectedCourse('');
			})
			.catch((err) => {
				setFormLoadingState(false);
				setSeatModal(false);
				setSelectedCourse('');
				console.log(err);
			});
	};

	const fetchEmployees = () => {
		const companyInfo = JSON.stringify({
			companyID: userInfo.companyID,
		});
		axios
			.post(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/companyUsers',
				companyInfo
			)
			.then((res) => {
				setAllEmployees(res.data.users);
			})
			.catch((err) => console.log(err));
	};

	useEffect(() => {
		const userInfo = JSON.parse(localStorage.getItem('ccAuth'));
		setLoading(true);
		if (userInfo.role === 'admin') {
			const companyInfo = JSON.stringify({
				companyID: userInfo.companyID,
			});
			const fetchEmployees = () => {
				axios
					.post(
						'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/companyUsers',
						companyInfo
					)
					.then((res) => {
						setAllEmployees(res.data.users);
					})
					.catch((err) => console.log(err));
			};

			const fetchAllCourses = () => {
				axios
					.get(
						'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/courses'
					)
					.then((res) => {
						setCourses(res.data.courses);
						setLoading(false);
					})
					.catch((err) => {
						setLoading(false);
					});
			};

			fetchEmployees();
			fetchAllCourses();
			return;
		}

		const userID = JSON.stringify({
			userID: userInfo.userID,
		});
		const fetchEnrolledCourses = () => {
			axios
				.post(
					'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/enrolledCourses',
					userID
				)
				.then((res) => {
					setCourses(res.data.enrolledCourses);
					setLoading(false);
				})
				.catch((err) => {
					setLoading(false);
				});
		};
		fetchEnrolledCourses();
	}, []);

	const adminCreateUser = (data) => {
		const myFormData = data;
		myFormData.companyID = userInfo.companyID;

		const userData = JSON.stringify(myFormData);
		setFormLoadingState(true);

		axios
			.post(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/user',
				userData
			)
			.then((res) => {
				console.log(res);
				fetchEmployees();
				setFormLoadingState(false);
				setShowModal(false);
			})
			.catch((err) => {
				console.log(err);
				setShowModal(false);
			});
	};
	return (
		<section className={classes.Overview}>
			<div className={classes.CoursesContainer}>
				{userInfo.role === 'admin' && (
					<div className={classes.Admin}>
						<h2 className={classes.CompanyName}>{userInfo.companyName}</h2>
						<div className={classes.EmployeeBox}>
							<h5>Employee Accounts</h5>
							<div className={classes.EmployeeList}>
								{allEmployee.length > 0 ? (
									allEmployee.map((employee) => (
										<div className={classes.Employee} key={employee.userFirstName}>
											<p className={classes.Name}>{employee.userFirstName}</p>
											<p className={classes.Email}>{employee.userEmail}</p>
										</div>
									))
								) : (
									<p>No Employee Account</p>
								)}
								<button onClick={() => setShowModal(true)} className={classes.AssignCourseBtn}>
									Add Employee
								</button>
							</div>
						</div>
					</div>
				)}
				<div className={classes.CourseList}>
					<h2 className={classes.Heading}>My Courses</h2>

					<div className={classes.CardContainer}>
						{courses.length > 0 &&
							courses.map((course) => (
								<OverviewCard
									key={course.courseName}
									courseName={course.courseName}
									showModal={() => onSelectCourseToAssign(course.courseID)}
									userInfo={userInfo}
								/>
							))}
						{loading && <Loader />}
					</div>
				</div>
			</div>
			{showModal && (
				<Modal>
					<form className={classes.Form} onSubmit={handleSubmit(adminCreateUser)}>
						<h6 onClick={() => setShowModal(false)}>Create New Employee Account</h6>
						<div className={classes.InputContainer}>
							<label htmlFor="firstName">Employee's First Name</label>
							<input
								type="text"
								name="firstName"
								id="firstName"
								placeholder="John"
								{...register('firstName')}
							/>
						</div>
						<div className={classes.InputContainer}>
							<label htmlFor="lastName">Employee's Last Name</label>
							<input
								type="text"
								name="lastName"
								id="lastName"
								placeholder="Doe"
								{...register('lastName')}
							/>
						</div>
						<div className={classes.InputContainer}>
							<label htmlFor="email">Employee's Email</label>
							<input
								type="email"
								name="email"
								id="email"
								placeholder="email@test.com"
								{...register('email')}
							/>
						</div>
						<div className={classes.InputContainer}>
							<label htmlFor="tel">Employee's Number</label>
							<input
								type="text"
								name="tel"
								id="tel"
								placeholder="070XXXXXXX"
								{...register('tel')}
							/>
						</div>
						{!formLoadingState && <button type="submit">Create Account</button>}
						{formLoadingState && <Loader />}
					</form>
				</Modal>
			)}
			{seatModal && (
				<Modal>
					<div className={classes.EmployeeBox}>
						<h5>Assign Course to:</h5>
						<div className={classes.EmployeeList}>
							{allEmployee.length > 0 ? (
								allEmployee.map((employee) => (
									<button
										onClick={() => onAssignSeat(employee.userID)}
										className={classes.AssignUserBtn}
									>
										<div className={classes.Employee} key={employee.userFirstName}>
											<p className={classes.Name}>{employee.userFirstName}</p>
											<p className={classes.Email}>{employee.userEmail}</p>
										</div>
									</button>
								))
							) : (
								<p>No Employee Account</p>
							)}
							<button
								onClick={() => {
									setSeatModal(false);
									setShowModal(true);
								}}
								className={classes.AssignCourseBtn}
							>
								Add Employee
							</button>
						</div>
						{formLoadingState && <Loader />}
					</div>
				</Modal>
			)}
		</section>
	);
};

export default Overview;
