import { Link } from 'react-router-dom';
import classes from './LandingPage.module.css';
import HeroImage from '../../../images/home-hero-base.svg';

const LandingPage = () => {
	return (
		<>
			<header className={classes.Header}>
				<div className={classes.HeaderText}>
					<h1>Empower your contact center staff to provide outstanding customer service.</h1>
					<p>
						We help your workforce reach their full potential with engaging online training. We
						train thousands of employees every year for companies that take customer service
						seriously.
					</p>
					<Link to="/courses">explore our courses</Link>
				</div>
				<div className={classes.HeaderImage}>
					<img src={HeroImage} alt="The Call Center" />
				</div>
			</header>
			<main>
				<div className={classes.Checklist}>
					<p>Highly scalable</p>
					<p>Learn anywhere, anytime</p>
					<p>Transparent progress</p>
					<p>Start training in minutes</p>
				</div>
			</main>
		</>
	);
};

export default LandingPage;
