import classes from './CourseCard.module.css';

const CourseCard = (props) => {
	return (
		<div className={classes.CourseCard}>
			<div className={classes.CourseCardImage}>
				<img src="../../../images/thumbnail.jpg" alt="Course Thumbnail" />
			</div>
			<div>
				<h4 className={classes.Title}>{props.title}</h4>
				<p className={classes.Desc}>{props.description}</p>

				<p className={classes.Price}>$ 24</p>
				<button className={classes.Button}>buy course</button>
			</div>
		</div>
	);
};

export default CourseCard;
