
(function(root){
const initial={id:'periode-contoh-01',start:'2026-09-01',end:'2026-09-30',pay:'2026-10-01'};
let records=[{...initial}];
function validDate(s){if(!/^\d{4}-\d{2}-\d{2}$/.test(s))return false;const d=new Date(s+'T00:00:00Z');return Number.isFinite(d.valueOf())&&d.toISOString().slice(0,10)===s;}
function validate(v){const errors={};for(const [k,label] of [['start','Tanggal mulai'],['end','Tanggal akhir'],['pay','Tanggal pembayaran']]){if(!v[k])errors[k]=label+' wajib diisi.';else if(!validDate(v[k]))errors[k]=label+' tidak valid.';}if(!errors.start&&!errors.end&&v.end<v.start)errors.end='Tanggal akhir tidak boleh sebelum tanggal mulai.';return errors;}
function save(v,id){const errors=validate(v);if(Object.keys(errors).length)return {ok:false,errors};if(id&&!records.some(r=>r.id===id))return {ok:false,errors:{form:'Periode tidak ditemukan.'}};const item={id:id||'periode-contoh-'+(records.length+1),start:v.start,end:v.end,pay:v.pay};if(id)records=records.map(r=>r.id===id?item:r);else records.push(item);return {ok:true,item:{...item}};}
const api={validate,save,list:()=>records.map(r=>({...r})),get:id=>{const r=records.find(r=>r.id===id);return r?{...r}:null;},reset:()=>{records=[{...initial}];}};
if(typeof module!=='undefined'&&module.exports)module.exports=api;else root.periodModel=api;
})(typeof window!=='undefined'?window:globalThis);
