
export function survey_name(name) {
  if(name.length<4) { 
    return { ok:false, error:"too short" }; 
  }
  if(!/^[a-zA-Z0-9., -]+$/.test(name)) {
    return { ok:false, error:"invalid name" }; 
  }
  return {ok:true};
}
