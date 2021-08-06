import CourseCard from '../../layout/CourseCard/CourseCard';
import classes from './Courses.module.css';

const Courses = () => {
	return (
		<section className={classes.Courses}>
			<div>
				<h2>Get Started with this</h2>
				<div className={classes.FeaturedCourse}>
					<div className={classes.FeaturedCourseText}>
						<h3>Modern React with Redux</h3>
						<p className={classes.Paragraph}>
							Thousands of other engineers have learned React and Redux, and you can too. This
							course uses a time-tested, battle-proven method to make sure you understand exactly
							how React and Redux work, and will get you a new job working as a software engineer or
							help you build that app you've always been dreaming about.
						</p>
						<div className={classes.Ratings}>
							<p className={classes.Price}>Free</p>
							<p className={classes.Stars}>5 star rating</p>
						</div>
						<button className={classes.Btn}>Enroll Now</button>
					</div>
					<div className={classes.FeaturedCourseImage}>
						<img src="../../../images/reactredux.jpeg" alt="Modern React" />
					</div>
				</div>
			</div>

			<div>
				<h2>New Courses</h2>
				<div className={classes.NewCourses}>
					<CourseCard
						title="The Complete Node.js Developer Course (3rd Edition)"
						description="Learn Node.js by building real-world applications with Node JS, Express, MongoDB, Jest, and more!"
					/>
					<CourseCard
						title="The Complete Node.js Developer Course (3rd Edition)"
						description="Learn Node.js by building real-world applications with Node JS, Express, MongoDB, Jest, and more!"
					/>
					<CourseCard
						title="The Complete Node.js Developer Course (3rd Edition)"
						description="Learn Node.js by building real-world applications with Node JS, Express, MongoDB, Jest, and more!"
					/>
				</div>
			</div>

			<div>
				<h2>All Courses</h2>
				<div className={classes.Filters}>
					<h5>Filter By</h5>
					<div>
						<input type="radio" name="filters" id="all" value="all" checked />
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
					<CourseCard
						title="The Complete Node.js Developer Course (3rd Edition)"
						description="Learn Node.js by building real-world applications with Node JS, Express, MongoDB, Jest, and more!"
					/>
					<CourseCard
						title="The Complete Node.js Developer Course (3rd Edition)"
						description="Learn Node.js by building real-world applications with Node JS, Express, MongoDB, Jest, and more!"
					/>
					<CourseCard
						title="The Complete Node.js Developer Course (3rd Edition)"
						description="Learn Node.js by building real-world applications with Node JS, Express, MongoDB, Jest, and more!"
					/>
				</div>
			</div>
		</section>
	);
};

export default Courses;
