import { Route, Switch } from 'react-router-dom';
import './App.css';
import Navbar from './components/layout/Navbar/Navbar';
import LandingPage from './components/pages/LandingPage/LandingPage';
import Login from './components/pages/Login/Login';

function App() {
	return (
		<div className="App">
			<Navbar />
			<Switch>
				<Route exact path="/" component={LandingPage} />
				<Route exact path="/login" component={Login} />
			</Switch>
		</div>
	);
}

export default App;
