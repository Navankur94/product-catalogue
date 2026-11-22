import { Search, ShoppingBag } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
export function Header(){const [term,setTerm]=useState('');const navigate=useNavigate();function submit(e:FormEvent){e.preventDefault();navigate(term.trim()?`/search?q=${encodeURIComponent(term.trim())}`:'/');}
return <header className="site-header"><Link to="/" className="brand"><span className="brand-mark"><ShoppingBag size={18}/></span><span>NORTHSTAR<span className="brand-dot">.</span></span></Link><nav><Link to="/">Discover</Link><a href="#categories">Categories</a></nav><form className="search-box" onSubmit={submit}><Search size={17}/><input aria-label="Search products" placeholder="Search the collection" value={term} onChange={e=>setTerm(e.target.value)}/><button aria-label="Submit search">↵</button></form></header>}
