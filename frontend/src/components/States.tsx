export function Loading(){return <div className="state">Loading the collection…</div>}
export function ErrorState({message}:{message:string}){return <div className="state error">{message}</div>}
export function EmptyState(){return <div className="state">No products found. Try another search.</div>}
