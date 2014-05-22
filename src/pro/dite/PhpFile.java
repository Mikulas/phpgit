package pro.dite;

import org.antlr.runtime.RecognitionException;
import org.antlr.v4.runtime.*;
import org.antlr.v4.runtime.tree.ParseTree;
import org.antlr.v4.tool.Grammar;
import org.eclipse.jgit.diff.RawText;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;

public class PhpFile
{

    public PhpFile(byte[] content) throws RecognitionException, IOException
    {
        InputStream is = new ByteArrayInputStream(content);
        ANTLRInputStream ais = new ANTLRInputStream(is);

        Grammar g = Grammar.load("zend_language_parser.phpy");
        LexerInterpreter lexEngine = g.createLexerInterpreter(ais);
        CommonTokenStream tokens = new CommonTokenStream(lexEngine);
        ParserInterpreter parser = g.createParserInterpreter(tokens);
        ParseTree t = parser.parse(0);
        System.out.println("parse tree: "+t.toStringTree(parser));
    }

    public class RawTextExt extends RawText
    {
        public RawTextExt(byte[] input)
        {
            super(input);
        }

        public String toString()
        {
            return new String(content);
        }
    }

}
