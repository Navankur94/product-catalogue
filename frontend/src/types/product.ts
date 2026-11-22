export interface Product { id:number; title:string; price:number; description:string; category:string; image:string; rating?:{rate:number;count:number} }
export interface EnquiryPayload { product_id:number; product_title:string; name:string; email:string; phone?:string; message?:string }
