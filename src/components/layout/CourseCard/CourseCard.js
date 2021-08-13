import { Link } from 'react-router-dom';
import classes from './CourseCard.module.css';
import { ReactComponent as Cart } from '../../../images/shopping-cart.svg';

const CourseCard = (props) => {
	return (
		<div className={classes.CourseCard}>
			<div className={classes.CourseCardImage}>
				<img src="../../../images/thumbnail.jpg" alt="Course Thumbnail" />
			</div>
			<div>
				<h4 className={classes.Title}>{props.title}</h4>
				<p className={classes.Desc}>{props.description}</p>

				<p className={classes.Price}>$ {props.price}</p>
				<div className={classes.BtnContainer}>
					<Link
						className={classes.Link}
						to={`/courses/${props.title}`}
						onClick={(e) => {
							if (props.courseCount) {
								e.preventDefault();
							}
						}}
					>
						Learn More
					</Link>
					<button onClick={() => alert('added to cart')} className={classes.Button}>
						<Cart />
					</button>
				</div>
			</div>
		</div>
	);
};

export default CourseCard;
