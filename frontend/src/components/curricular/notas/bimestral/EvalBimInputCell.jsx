import { memo } from 'react';
import NotaInputCell from '../NotaInputCell';

function EvalBimInputCell(props) {
  return <NotaInputCell {...props} />;
}

export default memo(EvalBimInputCell);
