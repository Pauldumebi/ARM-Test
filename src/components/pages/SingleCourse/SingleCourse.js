import classes from './SingleCourse.module.css';
import { useParams } from 'react-router-dom';
import { useEffect, useState } from 'react';
import axios from 'axios';
import Loader from '../../layout/Loader/Loader';

const SingleCourse = () => {
	const { courseName } = useParams();

	// console.log(courseCount);
	const [selectedCourse, setSelectedCourse] = useState(null);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);

	useEffect(() => {
		axios
			.get(
				'https://afternoon-ridge-35420.herokuapp.com/https://learningplatform.sandbox.9ijakids.com/api/api.php/courses'
			)
			.then((res) => {
				const allCourses = [...res.data.courses, ...res.data.bundles];

				const selected = allCourses.filter((course) => course.courseName === courseName);
				setSelectedCourse(...selected);
				console.log(selected);
				setLoading(false);
				setError(null);
			})
			.catch((err) => {
				setError('Something went wrong');
				setLoading(false);
			});
	}, [courseName]);

	return (
		<section className={classes.SingleCourse}>
			{selectedCourse && (
				<div className={classes.SelectedCourse}>
					<div className={classes.CourseDetails}>
						<div className={classes.CourseDetailsText}>
							<h3>{selectedCourse.courseName}</h3>
							<p className={classes.Paragraph}>{selectedCourse.courseDescription}</p>
						</div>
						<img
							alt={selectedCourse.courseName}
							src="../../../images/reactredux.jpeg"
							className={classes.CourseImage}
						/>
					</div>
					<div className={classes.PriceBox}>
						<p>$ {selectedCourse.price}</p>
						<p>{selectedCourse.duration}</p>
						{selectedCourse.courseType === 'Paid' && (
							<>
								<p>Certification</p>
								<button>buy course</button>
							</>
						)}
					</div>
					<div className={classes.Features}>
						<div className={classes.FeatureBox}>
							<h6>Who should take this?</h6>
							<ul>
								<li>New hires with no prior contact center experience (as part of onboarding)</li>
								<li>
									Employees that have difficulty understanding the inner workings of a contact
									center
								</li>
							</ul>
						</div>
						<div className={classes.FeatureBox}>
							<h6>Format</h6>
							<p>
								Self-paced e-learning containing an engaging mix of video, narratives, scenarios,
								and self-assessments. A certificate is provided if the mastery exam is passed
								successfully.
							</p>
						</div>
						<div className={classes.FeatureBox}>
							<h6>Time to complete</h6>
							<ul>
								<li>6 months from starting the first module</li>
								<li>Course contains 1 hour of content</li>
								<li>
									The course will remain available for the entire 6 months, even after completion
								</li>
							</ul>
						</div>
					</div>
				</div>
			)}
			{loading && !error && <Loader />}
			{error && !loading && <p>{error}</p>}
		</section>
	);
};

export default SingleCourse;
