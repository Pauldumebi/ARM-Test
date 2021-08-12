import { Link } from 'react-router-dom';
import classes from './Overview.module.css';

const Overview = () => {
	return (
		<section className={classes.Overview}>
			<h2 className={classes.Heading}>My Courses</h2>

			<div className={classes.CardContainer}>
				<div className={classes.OverviewCard}>
					<img src="../../../images/reactredux.jpeg" alt="Title of Course" />
					<div className={classes.Details}>
						<h4>Modern React</h4>
						<p>Learn React by building real-world applications.</p>
					</div>
					{/* <p>4.7 star</p> */}
					<Link className={classes.Link}>view course</Link>
				</div>
			</div>
		</section>
	);
};

export default Overview;
