// import { Link } from 'react-router-dom';
import classes from './Overview.module.css';

const OverviewCard = (props) => {
	return (
		<div className={classes.OverviewCard} key={props.courseName}>
			<img src="../../../images/reactredux.jpeg" alt="Title of Course" />
			<div className={classes.Details}>
				<h4>{props.courseName}</h4>
				{/* <p>Learn React by building real-world applications.</p> */}
			</div>
			{/* <p>4.7 star</p> */}
			<div className={classes.BtnContainer}>
				{/* <Link to={`/courses/${props.courseName}`} className={classes.Link}>
					view course
				</Link> */}
				<button className={classes.Link} onClick={props.showIframe}>
					Play
				</button>
				{props.userInfo.role === 'admin' && (
					<button onClick={() => props.showModal()} className={classes.AssignCourseBtn}>
						Assign Seat
					</button>
				)}
			</div>
		</div>
	);
};

export default OverviewCard;
