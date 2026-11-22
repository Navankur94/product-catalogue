import type { EnquiryPayload, Product } from '../types/product';
const BASE = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api';
async function request<T>(path:string, init?:RequestInit):Promise<T>{
  const response=await fetch(`${BASE}${path}`,{...init,headers:{Accept:'application/json','Content-Type':'application/json',...init?.headers}});
  const body=await response.json().catch(()=>({message:'Unexpected server response'}));
  if(!response.ok) throw new Error(body.message ?? 'Something went wrong');
  return body as T;
}
export const api={
  products:(q='')=>request<{data:Product[]}>(`/products${q?`?q=${encodeURIComponent(q)}`:''}`),
  featured:()=>request<{data:Product[]}>('/products/featured'),
  categories:()=>request<{data:string[]}>('/categories'),
  category:(name:string)=>request<{featured:Product[];products:Product[]}>(`/categories/${encodeURIComponent(name)}/products`),
  product:(id:number)=>request<{data:Product}>(`/products/${id}`),
  enquire:(payload:EnquiryPayload)=>request<{message:string}>('/enquiries',{method:'POST',body:JSON.stringify(payload)})
};
