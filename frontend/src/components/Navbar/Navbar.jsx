import React from "react";
import logo from '../../images/logo.png';
export default function Navbar() {
    return (
        <>
            <nav className='navbar'>
                <div id='logo'>
                    {/*<Link to="/">*/}
                        <img className="logo" src={logo} alt="logo"/>
                    {/*</Link>*/}
                </div>
            </nav>
        </>
    )
}