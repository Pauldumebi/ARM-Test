import axios from 'axios';
import { useEffect, useState } from 'react';
import { Link, useHistory } from 'react-router-dom';
import CourseCard from '../../layout/CourseCard/CourseCard';
import Loader from '../../layout/Loader/Loader';
import Modal from '../../layout/Modal/Modal';
import classes from './Courses.module.css';

const Courses = () => {
	const history = useHistory();
	const [showModal, setShowModal] = useState(false);
	const [courses, setCourses] = useState([]);
	const [bundles, setBundles] = useState([]);
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState(null);

	const enrollCourse = () => {
		const userInfo = localStorage.getItem('ccAuth');
		if (userInfo === null) return setShowModal(true);
		history.push('/overview');
	};

	useEffect(() => {
		setLoading(true);
		axios
			.get(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/courses'
			)
			.then((res) => {
				setCourses(res.data.courses);
				setBundles(res.data.bundles);
				console.log(res.data.bundles);
				setLoading(false);
				setError(null);
			})
			.catch((err) => {
				setError('Yikes! Something went wrong');
				setLoading(false);
			});
	}, []);
	return (
		<section className={classes.Courses}>
			{courses.length > 0 && (
				<>
					<div>
						<h2>Get Started with this</h2>
						<div className={classes.FeaturedCourse}>
							<div className={classes.FeaturedCourseText}>
								<h3>{courses[0].courseName}</h3>
								<p className={classes.Paragraph}>{courses[0].courseDescription}</p>
								<div className={classes.Ratings}>
									<p className={classes.Price}>Free</p>
									{/* <p className={classes.Stars}>5 star rating</p> */}
								</div>
								<div className={classes.CTA}>
									<button className={classes.Btn} onClick={enrollCourse}>
										Enroll Now
									</button>
									<Link
										className={classes.CTALink}
										to={{
											pathname: `/courses/${courses[0].courseName}`,
											courseCount: 1,
										}}
									>
										Learn More
									</Link>
								</div>
							</div>
							<div className={classes.FeaturedCourseImage}>
								<img src="../../../images/reactredux.jpeg" alt="Modern React" />
							</div>
						</div>
					</div>

					<div>
						<h2>All Courses</h2>
						<div className={classes.Filters}>
							<h5>Filter By</h5>
							<div>
								<input type="radio" name="filters" id="all" value="all" />
								<label htmlFor="all">All</label>
							</div>
							<div>
								<input type="radio" name="filters" id="css" value="css" />
								<label htmlFor="css">CSS</label>
							</div>
							<div>
								<input type="radio" name="filters" id="javascript" value="javascript" />
								<label htmlFor="javascript">JavaScript</label>
							</div>
							<div>
								<input type="radio" name="filters" id="php" value="php" />
								<label htmlFor="php">PHP</label>
							</div>
						</div>
						<div className={classes.NewCourses}>
							{courses.map((course, index) => {
								if (index === 0) return null;

								return (
									<CourseCard
										title={course.courseName}
										description={course.courseDescription}
										id={course.courseID}
										duration={course.duration}
										price={course.price}
										key={course.courseName}
									/>
								);
							})}
							{bundles.map((bundle, index) => {
								return (
									<CourseCard
										title={bundle.bundleTitle}
										description={bundle.bundleDescription}
										id={bundle.bundleID}
										duration={bundle.duration}
										price={bundle.price}
										courseCount={bundle.CourseCount}
										key={bundle.bundleTitle}
									/>
								);
							})}
						</div>
					</div>
				</>
			)}
			{loading && !error && <Loader />}
			{showModal && (
				<Modal hideModal={() => setShowModal(false)}>
					<div className={classes.ModalContent} onClick={() => setShowModal(false)}>
						<h6>
							You are not logged in!{' '}
							<Link to="/login" className={classes.Link}>
								Log In
							</Link>
						</h6>

						<h6>
							New User?{' '}
							<Link to="/signup" className={classes.Link}>
								Sign Up Here
							</Link>
						</h6>
					</div>
				</Modal>
			)}
			{error && !loading && <p>{error}</p>}
		</section>
	);
};

export default Courses;
