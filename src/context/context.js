import * as React from 'react';
import { reducer, initialState } from './reducer/reducer';

const GlobalStateContext = React.createContext();
const GlobalDispatchContext = React.createContext();

const ContextProvider = ({ children }) => {
	const [state, dispatch] = React.useReducer(reducer, initialState);
	return (
		<GlobalDispatchContext.Provider value={dispatch}>
			<GlobalStateContext.Provider value={state}>{children}</GlobalStateContext.Provider>
		</GlobalDispatchContext.Provider>
	);
};

export const useStateContext = () => React.useContext(GlobalStateContext);
export const useDispatchContext = () => React.useContext(GlobalDispatchContext);

export default ContextProvider;
